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
use FacturaScripts\Core\Session;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\Anticipo;

/**
 * Description of EditCliente
 *
 * @author Jorge-Prebac <info@smartcuines.com>
 */
class EditCliente
{
	protected function createViews(): Closure
	{
		return function() {
			$user = Session::get('user');
			if (!false == $user->can('ListAnticipo')) {
				//el usuario tiene acceso
				$this->createViewsListAnticipo();
			}
		};
	}
	
	protected function createViewsListAnticipo($viewName = 'ListAnticipo')
	{
		return function() {
			$viewName = 'ListAnticipo';
			$this->addListView($viewName, 'Anticipo', 'advance-payments', 'fa-solid fa-donate')
				->addOrderBy(['fecha'], 'date', 2)
				->addOrderBy(['fase'], 'phase')
				->addOrderBy(['importe'], 'amount');

			// si NO está activado el plugin Proyectos, desactivamos sus columnas
			if (false === class_exists('\\FacturaScripts\\Dinamic\\Model\\Proyecto')) {
				$this->views[$viewName]->disableColumn('project');
				$this->views[$viewName]->disableColumn('project-total-amount');
			}
		};
	}

    public function loadData(): Closure
    {
        return function($viewName, $view) {
            if ($viewName === 'ListAnticipo') {
                $codcliente = $this->getViewModelValue($this->getMainViewName(), 'codcliente');
                $where = [new DataBaseWhere('codcliente', $codcliente)];
                $view->loadData('', $where);

				// Localizamos anticipos sin vincular
				$anticiposCli = new Anticipo();
				$where = [
					new DataBaseWhere('codcliente', $codcliente, '='),
				];
				foreach($anticiposCli->all($where) as $anticipoCli) {
					if (false === ($anticipoCli->idpresupuesto || $anticipoCli->idpedido || $anticipoCli->idalbaran || $anticipoCli->idfactura)) {
						$itemAdv = Tools::trans('advance-not-linked', ['%idAnticipo%' =>$anticipoCli->id]);
						Tools::log()->warning("<a href='EditAnticipo?code=$anticipoCli->id' target='_blank'><i class='fa-solid fa-external-link-alt'></i> </a>" .  $itemAdv);
					}
				}
            }
        };
    }
}
