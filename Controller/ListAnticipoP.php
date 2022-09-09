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

use FacturaScripts\Core\Lib\ExtendedController\ListController;

/**
 * Description of ListAnticipoP
 *
 * @author Jorge-Prebac <info@prebac.com>
 */
class ListAnticipoP extends ListController
{

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData(): array
    {
        $data = parent::getPageData();
        $data['menu'] = 'purchases';
        $data['title'] = 'advance-payments-p';
        $data['icon'] = 'fas fa-donate';
        return $data;
    }

    protected function createViews()
    {
        $this->createViewsAnticiposP();
    }

    /**
     *
     * @param string $viewName
     */
    protected function createViewsAnticiposP(string $viewName = 'ListAnticipoP')
    {
        $this->addView($viewName, 'AnticipoP', 'advance-payments-p', 'fas fa-donate');
        $this->addSearchFields($viewName, ['id', 'fecha', 'fase', 'nota']);
        $this->addOrderBy($viewName, ['fecha'], 'date', 2);
        $this->addOrderBy($viewName, ['fase'], 'phase');
        $this->addOrderBy($viewName, ['importe'], 'amount');

        // Filtros
        $users = $this->codeModel->all('users', 'nick', 'nick');
        $this->addFilterSelect($viewName, 'user', 'user', 'user', $users);
        $this->addFilterAutocomplete($viewName, 'codproveedor', 'supplier', 'codproveedor', 'proveedores', 'codproveedor', 'nombre');
        $this->addFilterAutocomplete($viewName, 'fase', 'phase', 'fase', 'anticiposp', 'fase', 'fase');
        $this->addFilterPeriod($viewName, 'fecha', 'period', 'fecha');
    }
}