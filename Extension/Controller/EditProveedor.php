<?php
/**
 * This file is part of Anticipos plugin for FacturaScripts
 * Copyright (C) 2025 Carlos Garcia Gomez <carlos@facturascripts.com>
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

namespace FacturaScripts\Plugins\Anticipos\Extension\Controller;

use Closure;
use FacturaScripts\Core\Plugins;
use FacturaScripts\Core\Session;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\AnticipoP;

/**
 * Description of EditProveedor
 *
 * @author Jorge-Prebac <info@smartcuines.com>
 */
class EditProveedor
{
	protected function createViews(): Closure
	{
		return function() {
			$user = Session::get('user');
			if (!false == $user->can('ListAnticipoP')) {
				//el usuario tiene acceso
				$this->createViewsListAnticipoP();
			}
		};
	}
	
	protected function createViewsListAnticipoP($viewName = 'ListAnticipoP')
	{
		return function() {
			$viewName = 'ListAnticipoP';
			$this->addListView($viewName, 'AnticipoP', 'supplier-advance-payments', 'fa-solid fa-donate')
				->addOrderBy(['fecha'], 'date', 2)
				->addOrderBy(['fase'], 'phase')
				->addOrderBy(['importe'], 'amount');

			// si NO está activado el plugin Proyectos, desactivamos su columna
			if (false === Plugins::isEnabled('Proyectos')) {
				$this->views[$viewName]->disableColumn('project');
			}
		};
	}

    public function loadData(): Closure
    {
        return function($viewName, $view) {
            if ($viewName === 'ListAnticipoP') {
                $codproveedor = $this->getViewModelValue($this->getMainViewName(), 'codproveedor');
                $where = [new DataBaseWhere('codproveedor', $codproveedor)];
                $view->loadData('', $where);

				// Localizamos anticipos sin vincular
				$anticiposProv = new AnticipoP();
				$where = [
					new DataBaseWhere('codproveedor', $codproveedor, '='),
				];
				foreach($anticiposProv->all($where) as $anticipoProv) {
					if (false === ($anticipoProv->idpresupuesto || $anticipoProv->idpedido || $anticipoProv->idalbaran || $anticipoProv->idfactura)) {
						$itemAdv = Tools::trans('advance-not-linked', ['%idAnticipo%' =>$anticipoProv->id]);
						Tools::log()->warning("<a href='EditAnticipoP?code=$anticipoProv->id' target='_blank'><i class='fa-solid fa-external-link-alt'></i> </a>" .  $itemAdv);
					}
				}
            }
        };
    }
}
