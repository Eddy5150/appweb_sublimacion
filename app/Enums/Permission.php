<?php

namespace App\Enums;

enum Permission: string
{
    case PanelVer = 'panel.ver';

    case ProductosVer = 'productos.ver';
    case ProductosGestionar = 'productos.gestionar';

    case PedidosVer = 'pedidos.ver';
    case PedidosGestionar = 'pedidos.gestionar';

    case ClientesVer = 'clientes.ver';
    case ClientesGestionar = 'clientes.gestionar';

    case ReportesVer = 'reportes.ver';

    case UsuariosGestionar = 'usuarios.gestionar';

    case ProveedoresVer = 'proveedores.ver';
    case ProveedoresGestionar = 'proveedores.gestionar';

    case ComprasVer = 'compras.ver';
    case ComprasGestionar = 'compras.gestionar';

    case CajaVer = 'caja.ver';
    case CajaGestionar = 'caja.gestionar';

    case AuditoriaVer = 'auditoria.ver';

    public static function valuesForAdministrador(): array
    {
        return [
            self::PanelVer->value,
            self::ProductosVer->value,
            self::ProductosGestionar->value,
            self::PedidosVer->value,
            self::PedidosGestionar->value,
            self::ClientesVer->value,
            self::ClientesGestionar->value,
            self::ReportesVer->value,
            self::UsuariosGestionar->value,
            self::ProveedoresVer->value,
            self::ProveedoresGestionar->value,
            self::ComprasVer->value,
            self::ComprasGestionar->value,
            self::CajaVer->value,
            self::CajaGestionar->value,
            self::AuditoriaVer->value,
        ];
    }

    public static function valuesForCajera(): array
    {
        return [
            self::PanelVer->value,
            self::ProductosVer->value,
            self::PedidosVer->value,
            self::PedidosGestionar->value,
            self::ClientesVer->value,
            self::ClientesGestionar->value,
            self::CajaVer->value,
            self::CajaGestionar->value,
        ];
    }

    public static function valuesForOperador(): array
    {
        return [
            self::PanelVer->value,
            self::PedidosVer->value,
            self::ProductosVer->value,
        ];
    }

    public static function valuesForVendedor(): array
    {
        return [
            self::PanelVer->value,
            self::ProductosVer->value,
            self::PedidosVer->value,
            self::ClientesVer->value,
            self::ClientesGestionar->value,
            self::CajaVer->value,
        ];
    }
}
