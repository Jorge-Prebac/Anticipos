<?php
/**
 * This file is part of Anticipos plugin for FacturaScripts
 * Copyright (C) 2022 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Plugins\Anticipos;

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Dinamic\Model\Cliente;

/**
 * Description of Init
 *
 * @author Jorge-Prebac <info@prebac.com>
 */
class Init extends InitClass
{

    public function init()
    {
        $this->loadExtension(new Extension\Controller\EditCliente());
        $this->loadExtension(new Extension\Controller\EditPresupuestoCliente());
        $this->loadExtension(new Extension\Controller\EditPedidoCliente());
        $this->loadExtension(new Extension\Controller\EditAlbaranCliente());
        $this->loadExtension(new Extension\Controller\EditFacturaCliente());
        $this->loadExtension(new Extension\Model\PresupuestoCliente());
        $this->loadExtension(new Extension\Model\PedidoCliente());
        $this->loadExtension(new Extension\Model\AlbaranCliente());
        $this->loadExtension(new Extension\Model\FacturaCliente());

        if (class_exists('\\FacturaScripts\\Dinamic\\Model\\Proyecto')) {
            $this->loadExtension(new Extension\Controller\EditProyecto());
        }
    }

    public function update()
    {
        $this->setupSettings();
    }

    private function setupSettings()
    {
        $appsettings = $this->toolBox()->appSettings();
        if (empty($appsettings->get('anticipos', 'pdAnticipos'))) {
            $appsettings->set('anticipos', 'pdAnticipos', false);
            $appsettings->set('anticipos', 'level', 20);
        }
        $appsettings->save();
    }
}