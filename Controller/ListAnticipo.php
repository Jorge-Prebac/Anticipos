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

namespace FacturaScripts\Plugins\Anticipos\Controller;

use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Lib\ExtendedController\ListController;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\DataSrc\Empresas;

/**
 * Description of ListAnticipo
 *
 * @author Jorge-Prebac <info@prebac.com>
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
        $data['icon'] = 'fas fa-donate';
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
        $this->addView($viewName, 'Anticipo', 'advance-payments-c', 'fas fa-donate');
        $this->addSearchFields($viewName, ['fase', 'fecha', 'id', 'nota', 'user']);
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
		$this->addFilterAutocomplete($viewName, 'codcliente', 'customer', 'codcliente', 'clientes', 'codcliente', 'nombre');

		$users = $this->codeModel->all('users', 'nick', 'nick');
        $this->addFilterSelect($viewName, 'user', 'user', 'user', $users);
		
		$this->addFilterSelectWhere($viewName, 'status', [
            ['label' => Tools::lang()->trans('invoiced'), 'where' => []],
            ['label' => Tools::lang()->trans('generated-invoice'), 'where' => [new DataBaseWhere('idfactura', null, 'IS NOT')]],
            ['label' => Tools::lang()->trans('no-invoice'), 'where' => [new DataBaseWhere('idfactura', null)]],
        ]);

		// si no está instalado el plugin Proyectos ocultamos sus columnas, filtros y ordenación
		if (false === class_exists('\\FacturaScripts\\Dinamic\\Model\\Proyecto')) {
			$this->views[$viewName]->disableColumn('project');
		} else {
			$this->addFilterCheckbox($viewName, 'project', 'project', 'idproyecto', 'IS NOT', null);
			$this->addOrderBy($viewName, ['idproyecto'], 'project');
		}
    }
}