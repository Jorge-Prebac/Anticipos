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
use FacturaScripts\Core\Where;

/**
 * Description of EditProyecto
 *
 * @author Jorge-Prebac <info@smartcuines.com>
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
            $this->addListView($viewName, 'Anticipo', 'customer-advance-payments', 'fa-solid fa-donate')
				->addOrderBy(['fecha'], 'date', 2)
				->addOrderBy(['fase'], 'phase')
				->addOrderBy(['importe'], 'amount');
			$this->views[$viewName]->disableColumn('project');
        };
    }

    protected function createViewsAnticiposProv(): Closure
    {
        return function($viewName = 'ListAnticipoP') {
            $this->addListView($viewName, 'AnticipoP', 'supplier-advance-payments', 'fa-solid fa-donate')
				->addOrderBy(['fecha'], 'date', 2)
				->addOrderBy(['fase'], 'phase')
				->addOrderBy(['importe'], 'amount');
			$this->views[$viewName]->disableColumn('project');
        };
    }

	public function loadData(): Closure
	{
		return function($viewName, $view) {
			if (!in_array($viewName, ['ListAnticipo', 'ListAnticipoP'])) return;

			$mainView = $this->getMainViewName();
			$codCliente = $this->getViewModelValue($mainView, 'codcliente');
			
			// 1. Filtros base: Siempre por Proyecto y Empresa
			$where = [
				Where::eq('idproyecto', $this->getViewModelValue($mainView, 'idproyecto')),
				Where::eq('idempresa', $this->getViewModelValue($mainView, 'idempresa'))
			];

			// 2. Solo para ListAnticipo: Si el proyecto TIENE un cliente, filtramos por él
			// Si el proyecto NO tiene cliente, no añadimos este filtro (así salen todos)
			if ($viewName === 'ListAnticipo' && !empty($codCliente)) {
				$where[] = Where::eq('codcliente', $codCliente);
			}

			$view->loadData('', $where);
		};
}
}