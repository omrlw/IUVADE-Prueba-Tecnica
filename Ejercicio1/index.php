<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta charset="UTF-8" />  
<title>EJEMPLO IUVADE - Trabajadores</title>

<link rel="stylesheet" type="text/css" href="../extjs/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="../extjs/example.css" />
<link rel="stylesheet" type="text/css" href="../extjs/ux/css/CheckHeader.css" />

<script type="text/javascript" src="../extjs/bootstrap.js" charset="utf-8"></script>
<script type="text/javascript" src="../resources/locale/ext-lang-es.js" charset="utf-8"></script>

<script>
// Pantalla principal del CRUD de trabajadores.
// ExtJS en el frontend + endpoints PHP en /backend para cada operación.
Ext.onReady(function() {
    Ext.QuickTips.init();

    // 1) Modelo de datos: describe los campos tal como vienen del backend.
    Ext.define('Trabajador', {
        extend: 'Ext.data.Model',
        fields: [
            {name: 'tra_ide', type: 'int'},     // ID (PK)
            {name: 'tra_cod', type: 'int'},     // Código interno
            {name: 'tra_nom', type: 'string'},  // Nombre
            {name: 'tra_pat', type: 'string'},  // Apellido paterno
            {name: 'tra_mat', type: 'string'},  // Apellido materno
            {name: 'est_ado', type: 'int'}      // Estado (0 activo, 1 eliminado)
        ]
    });

    // 2) Store: se comunica con el endpoint PHP que lista los registros.
    //    ExtJS usará el proxy AJAX para pedir los datos a backend/trabajador_list.php.
    var trabajadorStore = Ext.create('Ext.data.Store', {
        model: 'Trabajador',
        autoLoad: true, // carga inmediata al abrir la página
        proxy: {
            type: 'ajax',
            url: 'backend/trabajador_list.php', // URL del backend (GET)
            reader: {
                type: 'json',
                root: 'data',            // propiedad del JSON que contiene el arreglo
                successProperty: 'success'
            }
        }
    });

    // 3) Formulario: se reutiliza para alta y edición (carga/limpia valores).
    var trabajadorForm = Ext.create('Ext.form.Panel', {
        bodyPadding: 10,
        defaults: {
            anchor: '100%',
            allowBlank: false
        },
        items: [
            { xtype: 'hiddenfield', name: 'tra_ide' }, // se usa solo al editar
            { xtype: 'numberfield', name: 'tra_cod', fieldLabel: 'Código' },
            { xtype: 'textfield', name: 'tra_nom', fieldLabel: 'Nombre' },
            { xtype: 'textfield', name: 'tra_pat', fieldLabel: 'Apellido paterno' },
            { xtype: 'textfield', name: 'tra_mat', fieldLabel: 'Apellido materno' }
        ]
    });

    // 4) Ventana modal que envuelve el formulario.
    //    El botón Guardar envía JSON al endpoint trabajador_save.php (insert/update).
    var trabajadorWindow = Ext.create('Ext.window.Window', {
        title: 'Trabajador',
        width: 400,
        modal: true,
        closeAction: 'hide',
        layout: 'fit',
        items: [trabajadorForm],
        buttons: [
            {
                text: 'Guardar',
                handler: function() {
                    if (!trabajadorForm.getForm().isValid()) return; // validación básica

                    var values = trabajadorForm.getValues(); // obtiene tra_ide, tra_cod, etc.

                    Ext.Ajax.request({
                        url: 'backend/trabajador_save.php', // mismo endpoint para nuevo/editar
                        method: 'POST',
                        jsonData: values, // se envía como JSON en el body
                        success: function(response){
                            var res = Ext.decode(response.responseText);
                            if (res.success) {
                                trabajadorWindow.hide();
                                trabajadorStore.reload(); // recarga la data del grid
                            } else {
                                Ext.Msg.alert('Error', res.message || 'Error al guardar');
                            }
                        },
                        failure: function(){
                            Ext.Msg.alert('Error', 'No se pudo guardar');
                        }
                    });
                }
            },
            {
                text: 'Cancelar',
                handler: function() {
                    trabajadorWindow.hide();
                }
            }
        ]
    });

    // 5) Grid principal: muestra los trabajadores y contiene las acciones CRUD.
    var grid = Ext.create('Ext.grid.Panel', {
        title: 'Trabajadores',
        store: trabajadorStore,            // se alimenta con el store que llama al backend
        renderTo: Ext.getBody(),
        width: 700,
        height: 400,
        selModel: 'rowmodel',              // permite seleccionar filas completas
        columns: [
            { text: 'ID',         dataIndex: 'tra_ide', width: 60 },
            { text: 'Código',     dataIndex: 'tra_cod', width: 80 },
            { text: 'Nombre',     dataIndex: 'tra_nom', flex: 1 },
            { text: 'Ap. Paterno', dataIndex: 'tra_pat', flex: 1 },
            { text: 'Ap. Materno', dataIndex: 'tra_mat', flex: 1 }
        ],
        tbar: [
            {
                text: 'Nuevo',
                handler: function() {
                    trabajadorForm.getForm().reset();  // limpia campos antes de insertar
                    trabajadorWindow.setTitle('Nuevo trabajador');
                    trabajadorWindow.show();
                }
            },
            {
                text: 'Modificar',
                handler: function() {
                    var record = grid.getSelectionModel().getSelection()[0];
                    if (!record) {
                        Ext.Msg.alert('Aviso', 'Selecciona un trabajador');
                        return;
                    }
                    // Carga los datos seleccionados en el form y abre la ventana.
                    trabajadorForm.getForm().loadRecord(record);
                    trabajadorWindow.setTitle('Modificar trabajador');
                    trabajadorWindow.show();
                }
            },
            {
                text: 'Eliminar',
                handler: function() {
                    var record = grid.getSelectionModel().getSelection()[0];
                    if (!record) {
                        Ext.Msg.alert('Aviso', 'Selecciona un trabajador');
                        return;
                    }

                    // Confirmación antes de hacer el borrado lógico (est_ado = 1).
                    Ext.Msg.confirm('Confirmar', '¿Eliminar trabajador?', function(btn) {
                        if (btn === 'yes') {
                            Ext.Ajax.request({
                                url: 'backend/trabajador_delete.php',
                                method: 'POST',
                                jsonData: { tra_ide: record.get('tra_ide') }, // se pasa el ID
                                success: function(response) {
                                    var res = Ext.decode(response.responseText);
                                    if (res.success) {
                                        trabajadorStore.reload();
                                    } else {
                                        Ext.Msg.alert('Error', res.message || 'Error al eliminar');
                                    }
                                },
                                failure: function() {
                                    Ext.Msg.alert('Error', 'No se pudo eliminar');
                                }
                            });
                        }
                    });
                }
            }
        ]
    });
});
</script>
</head>
<body>
</body>
</html>
