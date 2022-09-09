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

namespace FacturaScripts\Plugins\Anticipos\Extension\Model;

use Closure;
use FacturaScripts\Plugins\Anticipos\Model\AnticipoP;
use FacturaScripts\Core\Model\EstadoDocumento;
use FacturaScripts\Core\Model\DocTransformation;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of PresupuestoProveedor
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class PresupuestoProveedor
{
    public function saveUpdate(): Closure
    {
        return function () {
            $estado = new EstadoDocumento();
            $estado->loadFromCode($this->idestado);

            if (empty($estado->generadoc)) {
                return;
            }

            $anticiposPmodel = new AnticipoP();
            $where1 = [new DataBaseWhere('idpresupuesto', $this->idpresupuesto)];
            $anticiposP = $anticiposPmodel->all($where1, [], 0, 0);

            if (count($anticiposP) === 0) {
                return;
            }

            $newDoc = new DocTransformation();
            $where2 = [
                new DataBaseWhere('model1', 'PresupuestoProveedor'),
                new DataBaseWhere('iddoc1', $this->idpresupuesto)
            ];
            $newDoc->loadFromCode('', $where2);

            foreach ($anticiposP as $a) {
                switch ($newDoc->model2) {
                    case 'PresupuestoProveedor':
                        $a->idpresupuesto = $newDoc->iddoc2;
                        break;
                    case 'PedidoProveedor':
                        $a->idpedido = $newDoc->iddoc2;
                        break;
                    case 'AlbaranProveedor':
                        $a->idalbaran = $newDoc->iddoc2;
                        break;
                    case 'FacturaProveedor':
                        $a->idfactura = $newDoc->iddoc2;
                        break;
                }
                $a->save();
            }
        };
    }
}