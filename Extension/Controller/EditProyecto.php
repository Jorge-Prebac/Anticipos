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
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of EditProyecto
 *
 * @author Jorge-Prebac <info@prebac.com>
 */
class EditProyecto
{

	protected function createViews(): Closure
    {
        return function() {
			$user = Session::get('user');
			if (!false == $user->can('ListAnticipo')) {
				//el usuario tiene acceso
				$this->createViewsAnticiposCli();
			}

			if (!false == $user->can('ListAnticipoP')) {
				//el usuario tiene acceso
				$this->createViewsAnticiposProv();
			}
        };
    }

    protected function createViewsAnticiposCli(): Closure
    {
        return function($viewName = 'ListAnticipo') {
            $this->addListView($viewName, 'Anticipo', 'customer-advance-payments', 'fas fa-donate')
				->addOrderBy(['fecha'], 'date', 2)
				->addOrderBy(['fase'], 'phase')
				->addOrderBy(['importe'], 'amount');
        };
    }

    protected function createViewsAnticiposProv(): Closure
    {
        return function($viewName = 'ListAnticipoP') {
            $this->addListView($viewName, 'AnticipoP', 'supplier-advance-payments', 'fas fa-donate')
				->addOrderBy(['fecha'], 'date', 2)
				->addOrderBy(['fase'], 'phase')
				->addOrderBy(['importe'], 'amount');
        };
    }

    public function loadData(): Closure
    {
        return function($viewName, $view) {
            if ($viewName === 'ListAnticipo') {
                $codigo = $this->getViewModelValue($this->getMainViewName(), 'idproyecto');
                $where = [new DataBaseWhere('idproyecto', $codigo)];
                $view->loadData('', $where);

                if (empty ($this->views[$viewName]->model->codcliente)) {
                    $codcliente = $this->getViewModelValue($this->getMainViewName(), 'codcliente');
					$where = [
						new DataBaseWhere('codcliente', null),
						new DataBaseWhere('codcliente', $codcliente, '=', 'OR'),
					];
					$view->loadData('', $where);
                }

				if (empty ($this->views[$viewName]->model->idempresa)) {
                    $idempresa = $this->getViewModelValue($this->getMainViewName(), 'idempresa');
					$where = [
						new DataBaseWhere('idempresa', null),
						new DataBaseWhere('idempresa', $idempresa, '=', 'OR'),
					];
					$view->loadData('', $where);
                }
				
            }elseif ($viewName === 'ListAnticipoP') {
				$codigo = $this->getViewModelValue($this->getMainViewName(), 'idproyecto');
                $where = [new DataBaseWhere('idproyecto', $codigo)];
                $view->loadData('', $where);
				
				if (empty ($this->views[$viewName]->model->idempresa)) {
					$idempresa = $this->getViewModelValue($this->getMainViewName(), 'idempresa');
					$where = [
						new DataBaseWhere('idempresa', null),
						new DataBaseWhere('idempresa', $idempresa, '=', 'OR'),
					];
					$view->loadData('', $where);
				}
			}
        };
    }
}