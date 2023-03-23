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
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of EditProveedor
 *
 * @author Jorge-Prebac <info@prebac.com>
 */
class EditProveedor
{
	protected function createViews(): Closure
	{
		return function() {
			if ($this->user->can('ListAnticipoP')) {
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
                $codproveedor = $this->getViewModelValue($this->getMainViewName(), 'codproveedor');
                $where = [new DataBaseWhere('codproveedor', $codproveedor)];
                $view->loadData('', $where);
            }
        };
    }
}
