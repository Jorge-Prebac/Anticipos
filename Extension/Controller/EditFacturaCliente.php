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
use FacturaScripts\Dinamic\Model\Anticipo;

/**
 * Description of EditFacturaCliente
 *
 * @author Jorge-Prebac <info@prebac.com>
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class EditFacturaCliente
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
			$this->addListView($viewName, 'Anticipo', 'advance-payments', 'fas fa-donate')
				->addOrderBy(['fecha'], 'date', 2)
				->addOrderBy(['fase'], 'phase')
				->addOrderBy(['importe'], 'amount');
		};
	}

    public function loadData(): Closure
	{
        return function($viewName, $view) {
            if ($viewName === 'ListAnticipo') {
				$codigo = $this->getViewModelValue($this->getMainViewName(), 'idfactura');
				$where = [new DataBaseWhere('idfactura', $codigo)];
                $view->loadData('', $where);

				// Ocultamos botones de acción para que solo permita visualizar los anticipos, ya que están relacionados con los recibos de la factura.
				$this->setSettings($viewName, 'btnDelete', false);
				$this->setSettings($viewName, 'btnNew', false);
				$this->setSettings($viewName, 'checkBoxes', false);

				// Localizamos anticipos sin vincular
				$idempresa = $this->getViewModelValue($this->getMainViewName(), 'idempresa');
				$codcliente = $this->getViewModelValue($this->getMainViewName(), 'codcliente');
				$where = [
					new DataBaseWhere('codcliente', $codcliente, '='),
					new DataBaseWhere('idempresa', $idempresa, '=', 'AND'),
				];
				foreach(Anticipo::all($where) as $anticipoCli) {
					if (false === ($anticipoCli->idpresupuesto || $anticipoCli->idpedido || $anticipoCli->idalbaran || $anticipoCli->idfactura)) {
						$itemAdv = Tools::lang()->trans('advance-not-linked', ['%idAnticipo%' =>$anticipoCli->id]);
						Tools::log()->warning("<a href='EditAnticipo?code=$anticipoCli->id' target='_blank'><i class='fas fa-external-link-alt'></i> </a>" .  $itemAdv);
					}
				}
			}
        };
    }
}