<?php
/**
 * This file is part of Anticipos plugin for FacturaScripts
 * Copyright (C) 2020 Carlos Garcia Gomez <carlos@facturascripts.com>
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

/**
 * Description of Init
 *
 * @author Jorge-Prebac <info@prebac.com>
 */

use FacturaScripts\Core\Base\DataBase;
use FacturaScripts\Core\Base\InitClass;
use FacturaScripts\Dinamic\Model\Cliente;

/**
 * Description of Init of Anticipos
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

		$fileName = getcwd() . DIRECTORY_SEPARATOR . 'Dinamic'
							 . DIRECTORY_SEPARATOR . 'Controller'
							 . DIRECTORY_SEPARATOR . 'EditProyecto.php';

		if (file_exists ($fileName))
		{
			$this->loadExtension(new Extension\Controller\EditProyecto());
		}

    }
	
	public function update()
    {
		;
    }

}
