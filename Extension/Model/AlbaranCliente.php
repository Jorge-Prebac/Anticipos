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
use FacturaScripts\Plugins\Anticipos\Model\Anticipo;
use FacturaScripts\Core\Model\EstadoDocumento;
use FacturaScripts\Core\Model\DocTransformation;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;

/**
 * Description of AlbaranCliente
 *
 * @author Daniel Fernández Giménez <hola@danielfg.es>
 */
class AlbaranCliente
{
    public function saveUpdate(): Closure
    {
        return function () {
            $estado = new EstadoDocumento();
            $estado->loadFromCode($this->idestado);

            if (empty($estado->generadoc)) {
                return;
            }

            $anticiposModel = new Anticipo();
            $where1 = [new DataBaseWhere('idalbaran', $this->idalbaran)];
            $anticipos = $anticiposModel->all($where1, [], 0, 0);

            if (count($anticipos) === 0) {
                return;
            }

            $newDoc = new DocTransformation();
            $where2 = [
                new DataBaseWhere('model1', 'AlbaranCliente'),
                new DataBaseWhere('iddoc1', $this->idalbaran)
            ];
            $newDoc->loadFromCode('', $where2);

            foreach ($anticipos as $a) {
                switch ($newDoc->model2) {
                    case 'PresupuestoCliente':
                        $a->idpresupuesto = $newDoc->iddoc2;
                        break;
                    case 'PedidoCliente':
                        $a->idpedido = $newDoc->iddoc2;
                        break;
                    case 'AlbaranCliente':
                        $a->idalbaran = $newDoc->iddoc2;
                        break;
                    case 'FacturaCliente':
                        $a->idfactura = $newDoc->iddoc2;
                        break;
                }
                $a->save();
            }
        };
    }
}