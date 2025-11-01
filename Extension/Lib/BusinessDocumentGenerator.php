<?php
/**
 * This file is part of FacturaScripts
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

namespace FacturaScripts\Plugins\Anticipos\Extension\Lib;

use Closure;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base\TransformerDocument;
use FacturaScripts\Core\Tools;
use FacturaScripts\Dinamic\Lib\ReceiptGenerator;
use FacturaScripts\Dinamic\Model\Anticipo;
use FacturaScripts\Dinamic\Model\AnticipoP;
use FacturaScripts\Dinamic\Model\ReciboCliente;
use FacturaScripts\Dinamic\Model\ReciboProveedor;

/**
 * Description of BusinessDocumentGenerator
 *
 * @author Carlos García Gómez		<carlos@facturascripts.com>
 * @author Rafael San José Tovar		<rafael.sanjose@x-netdigital.com>
 * @author Raúl Jiménez						<raljopa@gmail.com>
 * @author Jorge-Prebac						<info@smartcuines.com>
 * @author Juan José Prieto Dzul		<juanjoseprieto88@gmail.com>
 */

class BusinessDocumentGenerator
{
    public function cloneLines(): Closure
    {
        return function ($prototype, $newDoc) {
            if ($newDoc instanceof TransformerDocument) {
                // Cambios para cuando se Agrupan o Parten documentos
                $this->copyAdvancePayment($newDoc);
            }
        };
    }

	public function copyAdvancePayment(): Closure
	{
		return function ($newDoc) {
			$anticipos = new AnticipoP();
			if (isset($newDoc->codcliente)) {
				$anticipos = new Anticipo();
			}
			foreach ($newDoc->parentDocuments() as $parent) {
				$where = [
					new DataBaseWhere($parent->primaryColumn(), $parent->id()),
					new DataBaseWhere($newDoc->primaryColumn(), NULL)
				];

				$order = ['id' => 'ASC'];

				foreach ($anticipos->all($where, $order) as $anticipo) {
					// Comprobamos que el anticipo no esté vinculado con una factura, ni con un documento del mismo tipo que el que estamos generando
					if ($anticipo->idfactura === null && $anticipo->{$newDoc->primaryColumn()} === null) {
						$anticipo->{$newDoc->primaryColumn()} = $newDoc->id();
						if (false === $anticipo->save()) {
							return false;
						}
					}
				}

				if (isset($newDoc->idfactura)) {
					//Eliminamos el recibo generado automáticamente.
					foreach ($newDoc->getReceipts() as $recibo) {
						$recibo->delete();
					}

					$invoiceWhere = [
						new DataBaseWhere($newDoc->primaryColumn(), $newDoc->id())
					];

					//Generamos los nuevos recibos en base a los anticipos.
					$numero = 1;
					foreach ($anticipos->all($invoiceWhere, $order) as $anticipoFac) {
						if (isset($newDoc->codcliente)) {
							$recibo = new ReciboCliente();
							$recibo->codcliente = $anticipoFac->codcliente;
						} else {
							$recibo = new ReciboProveedor();
							$recibo->codproveedor = $anticipoFac->codproveedor;
						}

						$recibo->coddivisa = $anticipoFac->coddivisa;
						$recibo->idempresa = $anticipoFac->idempresa;
						$recibo->idfactura = $anticipoFac->idfactura;
						$recibo->importe = $anticipoFac->importe;
						$recibo->nick = $anticipoFac->nick ? $anticipoFac->nick : $newDoc->nick;
						$recibo->numero = $numero++;
						$recibo->fecha = $anticipoFac->fecha;
						$recibo->codpago = $anticipoFac->codpago;
						$recibo->observaciones = $anticipoFac->nota;
						$recibo->pagado = 1;
						$recibo->vencimiento = $newDoc->fecha;

						if (true === (bool)Tools::settings('anticipos', 'pdAnticipos', true)) {
							$recibo->fechapago = $anticipoFac->fecha;
							$recibo->vencimiento = $anticipoFac->fecha;
						}
						$recibo->save();
					}

					//Generamos el recibo por el saldo pendiente si ubiese y actualizamos la factura.
					$generator = new ReceiptGenerator();
					$generator->update($newDoc);
				}
			}

			$where = [
				new DataBaseWhere($newDoc->primaryColumn(), $newDoc->id())
			];
			$newDoc->advance = count($anticipos->all($where, [], 0, 0));

			return true;
		};
	}
}
