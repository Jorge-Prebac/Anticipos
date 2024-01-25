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

namespace FacturaScripts\Plugins\Anticipos\Extension\Controller;

use Closure;
use FacturaScripts\Core\Session;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Dinamic\Model\AnticipoP;

/**
 * Description of EditFacturaProveedor
 *
 * @author Jorge-Prebac <info@prebac.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditFacturaProveedor
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
			$this->addListView($viewName, 'AnticipoP', 'supplier-advance-payments', 'fas fa-donate');
			$this->views[$viewName]->addOrderBy(['fecha'], 'date', 2);
			$this->views[$viewName]->addOrderBy(['fase'], 'phase');
			$this->views[$viewName]->addOrderBy(['importe'], 'amount');
		};
	}

    public function loadData(): Closure
	{
        return function($viewName, $view) {
            if ($viewName === 'ListAnticipoP') {
				$codigo = $this->getViewModelValue($this->getMainViewName(), 'idfactura');
				$where = [new DataBaseWhere('idfactura', $codigo)];
                $view->loadData('', $where);

				// Ocultamos botones de acción para que solo permita visualizar los anticipos, ya que están relacionados con los recibos de la factura.
				$this->setSettings($viewName, 'btnDelete', false);
				$this->setSettings($viewName, 'btnNew', false);
				$this->setSettings($viewName, 'checkBoxes', false);

				// Localizamos anticipos sin vincular
				$idempresa = $this->getViewModelValue($this->getMainViewName(), 'idempresa');
				$codproveedor = $this->getViewModelValue($this->getMainViewName(), 'codproveedor');
				$anticiposProv = new AnticipoP();
				$where = [
					new DataBaseWhere('codproveedor', $codproveedor, '='),
					new DataBaseWhere('idempresa', $idempresa, '=', 'AND'),
				];
				foreach($anticiposProv->all($where) as $anticipoProv) {
					if (false === ($anticipoProv->idpresupuesto || $anticipoProv->idpedido || $anticipoProv->idalbaran || $anticipoProv->idfactura)) {
						$itemAdv = Tools::lang()->trans('advance-not-linked', ['%idAnticipo%' =>$anticipoProv->id]);
						Tools::log()->warning("<a href='EditAnticipoP?code=$anticipoProv->id' target='_blank'><i class='fas fa-external-link-alt'></i> </a>" .  $itemAdv);
					}
				}
			}
        };
    }
}