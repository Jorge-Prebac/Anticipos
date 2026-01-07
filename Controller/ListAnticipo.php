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

namespace FacturaScripts\Plugins\Anticipos\Controller;

use FacturaScripts\Core\Plugins;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Lib\ExtendedController\ListController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\DataSrc\Empresas;

/**
 * Description of ListAnticipo
 *
 * @author Jorge-Prebac <info@smartcuines.com>
 */
class ListAnticipo extends ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'sales';
        $data['title'] = 'advance-payments-c';
        $data['icon'] = 'fa-solid fa-donate';
        return $data;
    }

    protected function createViews()
    {
        $this->createViewsAnticipos();
    }

    /**
     *
     * @param string $viewName
     */
    protected function createViewsAnticipos(string $viewName = 'ListAnticipo')
    {
        $this->addView($viewName, 'Anticipo', 'advance-payments-c', 'fa-solid fa-donate');
        $this->addSearchFields($viewName, ['fase', 'fecha', 'id', 'nota', 'nick']);
        $this->addOrderBy($viewName, ['fecha'], 'date', 2);
        $this->addOrderBy($viewName, ['fase'], 'phase');
        $this->addOrderBy($viewName, ['importe'], 'amount');

		// si solo hay una empresa, ocultamos la columna
        if (count(Empresas::all()) <= 1) {
            $this->views[$viewName]->disableColumn('company');
		}

		// Filtros
		// Si hay mas de una empresa, activamos su filtro
		if(count(Empresas::all()) > 1) {
			$companies = $this->codeModel->all('empresas', 'idempresa', 'nombre', true);
			$this->addFilterSelect($viewName, 'idempresa', 'company', 'idempresa', $companies);
        }
        $this->addFilterPeriod($viewName,  'period', 'date', 'fecha');
		$this->addFilterAutocomplete($viewName, 'fase', 'phase', 'fase', 'anticipos', 'fase', 'fase');
		$this->addFilterAutocomplete($viewName, 'codpago', 'method-payment', 'codpago', 'formaspago', 'codpago', 'descripcion');		
		$this->addFilterAutocomplete($viewName, 'codcliente', 'customer', 'codcliente', 'Cliente');

		$users = $this->codeModel->all('users', 'nick', 'nick');
        $this->addFilterSelect($viewName, 'nick', 'user', 'nick', $users);
		
		$this->addFilterSelectWhere($viewName, 'advances-status-list', [
            ['label' => Tools::trans('advances-status-list'), 'where' => []],
            ['label' => Tools::trans('generated-invoice'), 'where' => [new DataBaseWhere('idfactura', null, 'IS NOT')]],
            ['label' => Tools::trans('no-invoice'), 'where' => [new DataBaseWhere('idfactura', null)]],
			['label' => Tools::trans('advance-not-linked-list'), 'where' => [
					new DataBaseWhere('idpresupuesto', null),
					new DataBaseWhere('idpedido', null),
					new DataBaseWhere('idalbaran', null),
					new DataBaseWhere('idfactura', null),
				]],
        ]);

		// si está activado el plugin Proyectos incluimos filtros y ordenación.
		if (Plugins::isEnabled('Proyectos')) {
			$this->addFilterCheckbox($viewName, 'project', 'project', 'idproyecto', 'IS NOT', null);
			$this->addOrderBy($viewName, ['idproyecto'], 'project');			
		} else {
			// si NO está activado el plugin Proyectos, desactivamos su columna
			$this->views[$viewName]->disableColumn('project');
		}
    }
}