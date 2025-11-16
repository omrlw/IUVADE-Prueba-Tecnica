<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventas | Ejemplo IUVADE</title>
    <link rel="stylesheet" type="text/css" href="../extjs/resources/css/ext-all.css" />
    <link rel="stylesheet" type="text/css" href="../extjs/example.css" />
    <script type="text/javascript" src="../extjs/bootstrap.js" charset="utf-8"></script>
    <script type="text/javascript" src="../resources/locale/ext-lang-es.js" charset="utf-8"></script>
</head>
<body>
<script type="text/javascript">
// Configuración básica: requerimos los módulos que usaremos.
Ext.require([
    'Ext.grid.*',
    'Ext.data.*',
    'Ext.form.*',
    'Ext.window.*',
    'Ext.layout.container.Border'
]);

Ext.onReady(function () {
    Ext.QuickTips.init();

    // Modelo para la cabecera de ventas.
    Ext.define('Venta', {
        extend: 'Ext.data.Model',
        idProperty: 'ven_ide',
        fields: [
            { name: 'ven_ide', type: 'int' },
            { name: 'ven_ser', type: 'string' },
            { name: 'ven_num', type: 'string' },
            { name: 'ven_cli', type: 'string' },
            { name: 'ven_mon', type: 'float' },
            { name: 'est_ado', type: 'int' }
        ]
    });

    // Modelo para el detalle.
    Ext.define('VentaDetalle', {
        extend: 'Ext.data.Model',
        idProperty: 'v_d_ide',
        fields: [
            { name: 'v_d_ide', type: 'int' },
            { name: 'ven_ide', type: 'int' },
            { name: 'v_d_pro', type: 'string' },
            { name: 'v_d_uni', type: 'float' },
            { name: 'v_d_can', type: 'float' },
            { name: 'v_d_tot', type: 'float' },
            { name: 'est_ado', type: 'int' }
        ]
    });

    // Store de cabecera con carga automática desde el backend.
    const ventaStore = Ext.create('Ext.data.Store', {
        model: 'Venta',
        autoLoad: true,
        proxy: {
            type: 'ajax',
            url: 'backend/venta_list.php',
            reader: {
                type: 'json',
                root: 'data'
            }
        }
    });

    // Store de detalle (se carga cuando se selecciona una venta).
    const detalleStore = Ext.create('Ext.data.Store', {
        model: 'VentaDetalle',
        autoLoad: false,
        proxy: {
            type: 'ajax',
            url: 'backend/venta_detalle_list.php',
            extraParams: { ven_ide: 0 },
            reader: {
                type: 'json',
                root: 'data'
            }
        }
    });

    // Ventana reutilizable para crear o editar una venta.
    function mostrarFormularioVenta(record) {
        const esEditar = !!record;

        const win = Ext.create('Ext.window.Window', {
            title: esEditar ? 'Modificar venta' : 'Nueva venta',
            modal: true,
            layout: 'fit',
            width: 400,
            items: [{
                xtype: 'form',
                bodyPadding: 10,
                defaults: {
                    anchor: '100%',
                    allowBlank: false
                },
                items: [
                    { xtype: 'textfield', name: 'ven_ser', fieldLabel: 'Serie', value: record ? record.get('ven_ser') : '' },
                    { xtype: 'textfield', name: 'ven_num', fieldLabel: 'Número', value: record ? record.get('ven_num') : '' },
                    { xtype: 'textfield', name: 'ven_cli', fieldLabel: 'Cliente', value: record ? record.get('ven_cli') : '' },
                    { xtype: 'numberfield', name: 'ven_mon', fieldLabel: 'Monto', minValue: 0, decimalPrecision: 2, value: record ? record.get('ven_mon') : 0 }
                ],
                buttons: [{
                    text: 'Guardar',
                    formBind: true,
                    handler: function (btn) {
                        const form = btn.up('form').getForm();
                        const valores = form.getValues();

                        if (esEditar) {
                            valores.ven_ide = record.get('ven_ide');
                        }

                        Ext.Ajax.request({
                            url: 'backend/venta_save.php',
                            method: 'POST',
                            params: valores,
                            success: function (response) {
                                const res = Ext.decode(response.responseText);
                                if (res.success) {
                                    ventaStore.load();
                                    win.close();
                                } else {
                                    Ext.Msg.alert('Error', res.error || 'No se pudo guardar.');
                                }
                            },
                            failure: function () {
                                Ext.Msg.alert('Error', 'No se pudo contactar al servidor.');
                            }
                        });
                    }
                }, {
                    text: 'Cancelar',
                    handler: function () { win.close(); }
                }]
            }]
        });

        win.show();
    }

    // Ventana para crear o editar un detalle.
    function mostrarFormularioDetalle(record, ventaSeleccionada) {
        const esEditar = !!record;

        const win = Ext.create('Ext.window.Window', {
            title: esEditar ? 'Modificar detalle' : 'Nuevo detalle',
            modal: true,
            layout: 'fit',
            width: 420,
            items: [{
                xtype: 'form',
                bodyPadding: 10,
                defaults: {
                    anchor: '100%',
                    allowBlank: false
                },
                items: [
                    { xtype: 'displayfield', fieldLabel: 'Venta', value: ventaSeleccionada.get('ven_ide') },
                    { xtype: 'textfield', name: 'v_d_pro', fieldLabel: 'Producto', value: record ? record.get('v_d_pro') : '' },
                    { xtype: 'numberfield', name: 'v_d_uni', fieldLabel: 'Precio unit.', minValue: 0, decimalPrecision: 2, value: record ? record.get('v_d_uni') : 0 },
                    { xtype: 'numberfield', name: 'v_d_can', fieldLabel: 'Cantidad', minValue: 0, decimalPrecision: 2, value: record ? record.get('v_d_can') : 1 }
                ],
                buttons: [{
                    text: 'Guardar',
                    formBind: true,
                    handler: function (btn) {
                        const form = btn.up('form').getForm();
                        const valores = form.getValues();
                        valores.ven_ide = ventaSeleccionada.get('ven_ide');

                        if (esEditar) {
                            valores.v_d_ide = record.get('v_d_ide');
                        }

                        Ext.Ajax.request({
                            url: 'backend/venta_detalle_save.php',
                            method: 'POST',
                            params: valores,
                            success: function (response) {
                                const res = Ext.decode(response.responseText);
                                if (res.success) {
                                    detalleStore.load();
                                    win.close();
                                } else {
                                    Ext.Msg.alert('Error', res.error || 'No se pudo guardar el detalle.');
                                }
                            },
                            failure: function () {
                                Ext.Msg.alert('Error', 'No se pudo contactar al servidor.');
                            }
                        });
                    }
                }, {
                    text: 'Cancelar',
                    handler: function () { win.close(); }
                }]
            }]
        });

        win.show();
    }

    // Grid de cabecera de ventas.
    const ventaGrid = Ext.create('Ext.grid.Panel', {
        title: 'Cabecera de ventas',
        store: ventaStore,
        region: 'north',
        height: 280,
        split: true,
        columns: [
            { text: 'ID', dataIndex: 'ven_ide', width: 60 },
            { text: 'Serie', dataIndex: 'ven_ser', width: 80 },
            { text: 'Número', dataIndex: 'ven_num', width: 100 },
            { text: 'Cliente', dataIndex: 'ven_cli', flex: 1 },
            { text: 'Monto', dataIndex: 'ven_mon', width: 100, renderer: Ext.util.Format.numberRenderer('0,0.00') },
            { text: 'Estado', dataIndex: 'est_ado', width: 80 }
        ],
        tbar: [{
            text: 'Nuevo',
            handler: function () { mostrarFormularioVenta(null); }
        }, {
            text: 'Modificar',
            handler: function () {
                const record = ventaGrid.getSelectionModel().getSelection()[0];
                if (!record) {
                    Ext.Msg.alert('Aviso', 'Selecciona una venta.');
                    return;
                }
                mostrarFormularioVenta(record);
            }
        }, {
            text: 'Eliminar',
            handler: function () {
                const record = ventaGrid.getSelectionModel().getSelection()[0];
                if (!record) {
                    Ext.Msg.alert('Aviso', 'Selecciona una venta.');
                    return;
                }
                Ext.Msg.confirm('Confirmar', '¿Eliminar la venta seleccionada?', function (choice) {
                    if (choice === 'yes') {
                        Ext.Ajax.request({
                            url: 'backend/venta_delete.php',
                            method: 'POST',
                            params: { ven_ide: record.get('ven_ide') },
                            success: function (response) {
                                const res = Ext.decode(response.responseText);
                                if (res.success) {
                                    ventaStore.load();
                                    detalleStore.removeAll();
                                } else {
                                    Ext.Msg.alert('Error', res.error || 'No se pudo eliminar.');
                                }
                            },
                            failure: function () {
                                Ext.Msg.alert('Error', 'No se pudo contactar al servidor.');
                            }
                        });
                    }
                });
            }
        }]
    });

    // Grid de detalle por venta.
    const detalleGrid = Ext.create('Ext.grid.Panel', {
        title: 'Detalle de la venta seleccionada',
        store: detalleStore,
        region: 'center',
        columns: [
            { text: 'ID Det.', dataIndex: 'v_d_ide', width: 70 },
            { text: 'Producto', dataIndex: 'v_d_pro', flex: 1 },
            { text: 'P. unitario', dataIndex: 'v_d_uni', width: 100, renderer: Ext.util.Format.numberRenderer('0,0.00') },
            { text: 'Cantidad', dataIndex: 'v_d_can', width: 100, renderer: Ext.util.Format.numberRenderer('0,0.00') },
            { text: 'Total', dataIndex: 'v_d_tot', width: 100, renderer: Ext.util.Format.numberRenderer('0,0.00') },
            { text: 'Estado', dataIndex: 'est_ado', width: 80 }
        ],
        tbar: [{
            text: 'Nuevo detalle',
            handler: function () {
                const ventaSel = ventaGrid.getSelectionModel().getSelection()[0];
                if (!ventaSel) {
                    Ext.Msg.alert('Aviso', 'Selecciona primero una venta.');
                    return;
                }
                mostrarFormularioDetalle(null, ventaSel);
            }
        }, {
            text: 'Modificar detalle',
            handler: function () {
                const ventaSel = ventaGrid.getSelectionModel().getSelection()[0];
                const detalleSel = detalleGrid.getSelectionModel().getSelection()[0];
                if (!ventaSel) {
                    Ext.Msg.alert('Aviso', 'Selecciona primero una venta.');
                    return;
                }
                if (!detalleSel) {
                    Ext.Msg.alert('Aviso', 'Selecciona un detalle.');
                    return;
                }
                mostrarFormularioDetalle(detalleSel, ventaSel);
            }
        }, {
            text: 'Eliminar detalle',
            handler: function () {
                const detalleSel = detalleGrid.getSelectionModel().getSelection()[0];
                if (!detalleSel) {
                    Ext.Msg.alert('Aviso', 'Selecciona un detalle.');
                    return;
                }
                Ext.Msg.confirm('Confirmar', '¿Eliminar el detalle seleccionado?', function (choice) {
                    if (choice === 'yes') {
                        Ext.Ajax.request({
                            url: 'backend/venta_detalle_delete.php',
                            method: 'POST',
                            params: { v_d_ide: detalleSel.get('v_d_ide') },
                            success: function (response) {
                                const res = Ext.decode(response.responseText);
                                if (res.success) {
                                    detalleStore.load();
                                } else {
                                    Ext.Msg.alert('Error', res.error || 'No se pudo eliminar el detalle.');
                                }
                            },
                            failure: function () {
                                Ext.Msg.alert('Error', 'No se pudo contactar al servidor.');
                            }
                        });
                    }
                });
            }
        }]
    });

    // Cuando se selecciona una venta cargamos sus detalles.
    ventaGrid.getSelectionModel().on('selectionchange', function (selModel, records) {
        const ventaSel = records[0];
        if (ventaSel) {
            detalleStore.getProxy().setExtraParam('ven_ide', ventaSel.get('ven_ide'));
            detalleStore.load();
        } else {
            detalleStore.removeAll();
        }
    });

    // Viewport principal con layout border.
    Ext.create('Ext.container.Viewport', {
        layout: 'border',
        items: [
            { region: 'center', layout: 'border', items: [ventaGrid, detalleGrid] }
        ]
    });
});
</script>
</body>
</html>
