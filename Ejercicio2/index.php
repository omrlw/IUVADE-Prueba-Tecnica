<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta charset="UTF-8" />
<title>EJEMPLO IUVADE - Ventas</title>

<!-- Hojas de estilo básicas de ExtJS -->
<link rel="stylesheet" type="text/css" href="../extjs/resources/css/ext-all.css" />
<link rel="stylesheet" type="text/css" href="../extjs/example.css" />

<!-- Núcleo de ExtJS y traducción al español -->
<script type="text/javascript" src="../extjs/bootstrap.js" charset="utf-8"></script>
<script type="text/javascript" src="../resources/locale/ext-lang-es.js" charset="utf-8"></script>

<script>
// Pantalla maestro-detalle para gestionar ventas (cabecera) y sus líneas (detalle).
// Cada bloque va documentado para que sea fácil de seguir.
Ext.onReady(function() {
    Ext.QuickTips.init();

    // ----------------------------
    // 1) Modelos de datos
    // ----------------------------
    // Cabecera de venta: coincide con los campos entregados por el backend.
    Ext.define('Venta', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'ven_ide', type: 'int' },     // PK
            { name: 'ven_ser', type: 'string' },  // Serie del comprobante
            { name: 'ven_num', type: 'string' },  // Número del comprobante
            { name: 'ven_cli', type: 'string' },  // Cliente
            { name: 'ven_mon', type: 'float' },   // Monto total declarado
            { name: 'est_ado', type: 'int' }      // 1 activo / 0 eliminado lógico
        ]
    });

    // Detalle de venta: campo foráneo ven_ide enlaza con Venta.
    Ext.define('VentaDetalle', {
        extend: 'Ext.data.Model',
        fields: [
            { name: 'v_d_ide', type: 'int' },     // PK
            { name: 'ven_ide', type: 'int' },     // FK a venta
            { name: 'v_d_pro', type: 'string' },  // Producto/servicio
            { name: 'v_d_uni', type: 'float' },   // Precio unitario
            { name: 'v_d_can', type: 'float' },   // Cantidad
            { name: 'v_d_tot', type: 'float' },   // Total calculado en trigger
            { name: 'est_ado', type: 'int' }      // 1 activo / 0 eliminado lógico
        ]
    });

    // ----------------------------
    // 2) Stores que hablan con el backend
    // ----------------------------
    var ventaStore = Ext.create('Ext.data.Store', {
        model: 'Venta',
        autoLoad: true,
        proxy: {
            type: 'ajax',
            url: 'backend/venta_list.php',
            reader: {
                type: 'json',
                root: 'data',
                successProperty: 'success'
            }
        }
    });

    // Store de detalle: solo carga cuando hay una cabecera seleccionada.
    var detalleStore = Ext.create('Ext.data.Store', {
        model: 'VentaDetalle',
        autoLoad: false,
        proxy: {
            type: 'ajax',
            url: 'backend/detalle_list.php',
            extraParams: {
                ven_ide: null // se setea al seleccionar una venta
            },
            reader: {
                type: 'json',
                root: 'data',
                successProperty: 'success'
            }
        }
    });

    // ----------------------------
    // 3) Formularios reutilizables
    // ----------------------------
    var ventaForm = Ext.create('Ext.form.Panel', {
        bodyPadding: 10,
        defaults: {
            anchor: '100%',
            allowBlank: false
        },
        items: [
            { xtype: 'hiddenfield', name: 'ven_ide' }, // solo al editar
            { xtype: 'textfield', name: 'ven_ser', fieldLabel: 'Serie', maxLength: 10 },
            { xtype: 'textfield', name: 'ven_num', fieldLabel: 'Número', maxLength: 100 },
            { xtype: 'textfield', name: 'ven_cli', fieldLabel: 'Cliente' },
            { xtype: 'numberfield', name: 'ven_mon', fieldLabel: 'Monto', minValue: 0, decimalPrecision: 2 }
        ]
    });

    // Ventana para insertar/editar cabecera.
    var ventaWindow = Ext.create('Ext.window.Window', {
        title: 'Venta',
        width: 450,
        modal: true,
        closeAction: 'hide',
        layout: 'fit',
        items: [ventaForm],
        buttons: [
            {
                text: 'Guardar',
                handler: function() {
                    if (!ventaForm.getForm().isValid()) {
                        return;
                    }

                    var values = ventaForm.getValues();

                    Ext.Ajax.request({
                        url: 'backend/venta_save.php',
                        method: 'POST',
                        jsonData: values,
                        success: function(response) {
                            var res = Ext.decode(response.responseText);
                            if (res.success) {
                                ventaWindow.hide();
                                ventaStore.reload();   // refresca grid cabecera
                                detalleStore.removeAll(); // limpia detalle si cambia selección
                            } else {
                                Ext.Msg.alert('Error', res.message || 'No se pudo guardar');
                            }
                        },
                        failure: function() {
                            Ext.Msg.alert('Error', 'No se pudo guardar');
                        }
                    });
                }
            },
            {
                text: 'Cancelar',
                handler: function() {
                    ventaWindow.hide();
                }
            }
        ]
    });

    // Formulario para detalle, incluye ven_ide oculto para amarrarlo a cabecera.
    var detalleForm = Ext.create('Ext.form.Panel', {
        bodyPadding: 10,
        defaults: {
            anchor: '100%',
            allowBlank: false
        },
        items: [
            { xtype: 'hiddenfield', name: 'v_d_ide' },
            { xtype: 'hiddenfield', name: 'ven_ide' }, // se completa al abrir el form
            { xtype: 'textfield', name: 'v_d_pro', fieldLabel: 'Producto' },
            { xtype: 'numberfield', name: 'v_d_uni', fieldLabel: 'Precio unitario', minValue: 0, decimalPrecision: 2 },
            { xtype: 'numberfield', name: 'v_d_can', fieldLabel: 'Cantidad', minValue: 0, decimalPrecision: 2 }
        ]
    });

    var detalleWindow = Ext.create('Ext.window.Window', {
        title: 'Detalle de venta',
        width: 450,
        modal: true,
        closeAction: 'hide',
        layout: 'fit',
        items: [detalleForm],
        buttons: [
            {
                text: 'Guardar',
                handler: function() {
                    if (!detalleForm.getForm().isValid()) {
                        return;
                    }

                    var values = detalleForm.getValues();

                    Ext.Ajax.request({
                        url: 'backend/detalle_save.php',
                        method: 'POST',
                        jsonData: values,
                        success: function(response) {
                            var res = Ext.decode(response.responseText);
                            if (res.success) {
                                detalleWindow.hide();
                                // se recarga el detalle manteniendo la cabecera actual
                                detalleStore.load();
                            } else {
                                Ext.Msg.alert('Error', res.message || 'No se pudo guardar');
                            }
                        },
                        failure: function() {
                            Ext.Msg.alert('Error', 'No se pudo guardar');
                        }
                    });
                }
            },
            {
                text: 'Cancelar',
                handler: function() {
                    detalleWindow.hide();
                }
            }
        ]
    });

    // ----------------------------
    // 4) Grids y acciones
    // ----------------------------
    var ventaGrid = Ext.create('Ext.grid.Panel', {
        title: 'Cabecera de ventas',
        store: ventaStore,
        renderTo: Ext.getBody(),
        width: 900,
        height: 300,
        selModel: 'rowmodel',
        columns: [
            { text: 'ID', dataIndex: 'ven_ide', width: 60 },
            { text: 'Serie', dataIndex: 'ven_ser', width: 80 },
            { text: 'Número', dataIndex: 'ven_num', width: 100 },
            { text: 'Cliente', dataIndex: 'ven_cli', flex: 1 },
            { text: 'Monto declarado', dataIndex: 'ven_mon', width: 130, renderer: Ext.util.Format.usMoney }
        ],
        tbar: [
            {
                text: 'Nuevo',
                handler: function() {
                    ventaForm.getForm().reset();
                    ventaWindow.setTitle('Nueva venta');
                    ventaWindow.show();
                }
            },
            {
                text: 'Modificar',
                handler: function() {
                    var record = ventaGrid.getSelectionModel().getSelection()[0];
                    if (!record) {
                        Ext.Msg.alert('Aviso', 'Selecciona una venta');
                        return;
                    }
                    ventaForm.getForm().loadRecord(record);
                    ventaWindow.setTitle('Modificar venta');
                    ventaWindow.show();
                }
            },
            {
                text: 'Eliminar',
                handler: function() {
                    var record = ventaGrid.getSelectionModel().getSelection()[0];
                    if (!record) {
                        Ext.Msg.alert('Aviso', 'Selecciona una venta');
                        return;
                    }

                    Ext.Msg.confirm('Confirmar', '¿Eliminar venta?', function(btn) {
                        if (btn === 'yes') {
                            Ext.Ajax.request({
                                url: 'backend/venta_delete.php',
                                method: 'POST',
                                jsonData: { ven_ide: record.get('ven_ide') },
                                success: function(response) {
                                    var res = Ext.decode(response.responseText);
                                    if (res.success) {
                                        ventaStore.reload();
                                        detalleStore.removeAll(); // limpia detalle cuando ya no hay cabecera
                                    } else {
                                        Ext.Msg.alert('Error', res.message || 'No se pudo eliminar');
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
        ],
        listeners: {
            // Cada vez que se selecciona una cabecera se filtra el detalle por ven_ide.
            selectionchange: function(model, records) {
                var selected = records[0];
                if (selected) {
                    detalleStore.getProxy().setExtraParam('ven_ide', selected.get('ven_ide'));
                    detalleStore.load();
                } else {
                    detalleStore.removeAll();
                }
            }
        }
    });

    // Grid para los detalles de la venta seleccionada.
    var detalleGrid = Ext.create('Ext.grid.Panel', {
        title: 'Detalle de venta',
        store: detalleStore,
        renderTo: Ext.getBody(),
        width: 900,
        height: 300,
        selModel: 'rowmodel',
        columns: [
            { text: 'ID', dataIndex: 'v_d_ide', width: 60 },
            { text: 'Producto', dataIndex: 'v_d_pro', flex: 1 },
            { text: 'Unitario', dataIndex: 'v_d_uni', width: 100, renderer: Ext.util.Format.usMoney },
            { text: 'Cantidad', dataIndex: 'v_d_can', width: 100 },
            { text: 'Total', dataIndex: 'v_d_tot', width: 120, renderer: Ext.util.Format.usMoney }
        ],
        tbar: [
            {
                text: 'Nuevo detalle',
                handler: function() {
                    var ventaSeleccionada = ventaGrid.getSelectionModel().getSelection()[0];
                    if (!ventaSeleccionada) {
                        Ext.Msg.alert('Aviso', 'Primero selecciona una venta');
                        return;
                    }
                    detalleForm.getForm().reset();
                    detalleForm.getForm().setValues({ ven_ide: ventaSeleccionada.get('ven_ide') });
                    detalleWindow.setTitle('Nuevo detalle');
                    detalleWindow.show();
                }
            },
            {
                text: 'Modificar detalle',
                handler: function() {
                    var ventaSeleccionada = ventaGrid.getSelectionModel().getSelection()[0];
                    var detalleSeleccionado = detalleGrid.getSelectionModel().getSelection()[0];
                    if (!ventaSeleccionada) {
                        Ext.Msg.alert('Aviso', 'Selecciona una venta');
                        return;
                    }
                    if (!detalleSeleccionado) {
                        Ext.Msg.alert('Aviso', 'Selecciona un detalle');
                        return;
                    }
                    detalleForm.getForm().loadRecord(detalleSeleccionado);
                    detalleWindow.setTitle('Modificar detalle');
                    detalleWindow.show();
                }
            },
            {
                text: 'Eliminar detalle',
                handler: function() {
                    var detalleSeleccionado = detalleGrid.getSelectionModel().getSelection()[0];
                    if (!detalleSeleccionado) {
                        Ext.Msg.alert('Aviso', 'Selecciona un detalle');
                        return;
                    }

                    Ext.Msg.confirm('Confirmar', '¿Eliminar detalle?', function(btn) {
                        if (btn === 'yes') {
                            Ext.Ajax.request({
                                url: 'backend/detalle_delete.php',
                                method: 'POST',
                                jsonData: { v_d_ide: detalleSeleccionado.get('v_d_ide') },
                                success: function(response) {
                                    var res = Ext.decode(response.responseText);
                                    if (res.success) {
                                        detalleStore.load();
                                    } else {
                                        Ext.Msg.alert('Error', res.message || 'No se pudo eliminar');
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
