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
namespace FacturaScripts\Plugins\Anticipos\Controller;

use FacturaScripts\Core\Lib\ExtendedController\ListController;

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
    public function getPageData()
    {
        $data = parent::getPageData();
        $data['menu'] = 'sales';
        $data['title'] = 'advance_payments';
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
    protected function createViewsAnticipos($viewName = 'ListAnticipo')
    {
        $this->addView($viewName, 'Anticipo', 'advance_payments' ,'fas fa-donate');
        $this->addSearchFields($viewName, ['id', 'fecha', 'fase', 'nota']);
        $this->addOrderBy($viewName, ['fecha'], 'date', 2);
		$this->addOrderBy($viewName, ['fase'], 'phase');
        $this->addOrderBy($viewName, ['importe'], 'amount');

        ///Filtros
        $users = $this->codeModel->all('users','nick','nick');
        $this->addFilterSelect($viewName, 'user', 'user', 'user', $users);
        $this->addFilterAutocomplete($viewName, 'codcliente', 'customer', 'codcliente', 'clientes', 'codcliente', 'nombre');
		$this->addFilterAutocomplete($viewName, 'fase', 'phase', 'fase', 'anticipos', 'fase', 'fase');
        $this->addFilterPeriod($viewName, 'fecha', 'date', 'fecha');

    }
}