<?php
/**
 * This file is part of Anticipos plugin for FacturaScripts
 * Copyright (C) 2024 Carlos Garcia Gomez <carlos@facturascripts.com>
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

namespace FacturaScripts\Plugins\Anticipos\Model;

use FacturaScripts\Core\Session;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Model\Base;
use FacturaScripts\Core\Model\Base\ModelOnChangeClass;
use FacturaScripts\Core\Model\Base\ModelTrait;
use FacturaScripts\Dinamic\Model\AlbaranCliente;
use FacturaScripts\Dinamic\Model\Cliente;
use FacturaScripts\Dinamic\Model\FacturaCliente;
use FacturaScripts\Dinamic\Model\PedidoCliente;
use FacturaScripts\Dinamic\Model\PresupuestoCliente;

/**
 * Description of Anticipo
 *
 * @autor Jorge-Prebac                         <info@prebac.com>
 * @autor Daniel Fernández Giménez <hola@danielfg.es>
 * @autor Juan José Prieto Dzul           <juanjoseprieto88@gmail.com>
 */
class Anticipo extends ModelOnChangeClass
{
    use ModelTrait;

    protected $projectClass = '\\FacturaScripts\\Dinamic\\Model\\Proyecto';

    /** @return string */
    public $codcliente;

    /** @return string */
    public $coddivisa;

    /** @var integer */
    public $id;

    /** @return string */
    public $fase;

    /** @return string */
    public $fecha;

    /** @var integer */
    public $idalbaran;

    /** @var integer */
    public $idempresa;

    /** @var integer */
    public $idfactura;

    /** @var integer */
    public $idpedido;

    /** @var integer */
    public $idpresupuesto;

    /** @var integer */
    public $idproyecto;

    /** @var integer */
    public $idrecibo;

    /** @var float */
    public $importe;

    /** @return string */
    public $nota;

    /** @return string */
	public $nick;

    public function __get(string $name)
    {
        switch ($name) {
            case 'riesgomax':
                return $this->getSubject()->riesgomax;

            case 'totalrisk':
                return $this->getSubject()->riesgoalcanzado;

            case 'totaldelivery':
                $delivery = new AlbaranCliente();
                $delivery->loadFromCode($this->idalbaran);
                return $delivery->total;

            case 'totalestimation':
                $estimation = new PresupuestoCliente();
                $estimation->loadFromCode($this->idpresupuesto);
                return $estimation->total;

            case 'totalinvoice':
                $invoice = new FacturaCliente();
                $invoice->loadFromCode($this->idfactura);
                return $invoice->total;

            case 'totalorder':
                $order = new PedidoCliente();
                $order->loadFromCode($this->idpedido);
                return $order->total;

            case 'totalproject':
                if (class_exists($this->projectClass)) {
                    $project = new $this->projectClass();
                    $project->loadFromCode($this->idproyecto);
                    return $project->totalventas;
                }
                return 0;
        }
        return null;
    }

    public function clear()
    {
        parent::clear();
        $this->coddivisa = Tools::settings('default', 'coddivisa');
		$this->fecha = Tools::date();
        $this->importe = 0;
    }

    public static function primaryColumn(): string
    {
        return 'id';
    }

	public function primaryDescription(): string
	{
		return '#' . $this->id . ', ' . $this->fecha;
	}

    public static function tableName(): string
    {
        return 'anticipos';
    }

	protected function setPreviousData(array $fields = [])
    {
        $docs = ['idpresupuesto', 'idpedido', 'idalbaran', 'idfactura'];
		parent::setPreviousData(array_merge($docs, $fields));
    }

	protected function onInsert()
    {
		$onDeleted = false;

		// Save Total Advance Count
		$this->AdvanceData($onDeleted);

		// Save audit log
		$this->saveAuditMessage('inserted-model');

        parent::onInsert();
    }

    protected function onUpdate()
    {
		$onDeleted = false;

		//Save Total Advance Count
		$this->AdvanceData($onDeleted);

		// Save audit log
		$this->saveAuditMessage('updated-model');

        parent::onUpdate();
    }

	protected function onDelete()
    {
		$onDeleted = true;

		// Save Total Advance Count
		$this->AdvanceData($onDeleted,);

		// Save audit log
		$this->saveAuditMessage('deleted-model');

		$onDeleted = false;
		parent::onDelete();
    }

	protected function AdvanceData($onDeleted): void
    {
		$idDoc = 'idpresupuesto';
		$idValuePrev = $this->previousData['idpresupuesto'];
		$idValueNow = $this->idpresupuesto;
		if ($onDeleted || $idValuePrev != $idValueNow) {	
			$docModel = new PresupuestoCliente;
			$this->TotalAdvanceDocs($idDoc, $idValuePrev, $idValueNow, $docModel);
		}

		$idDoc = 'idpedido';
		$idValuePrev = $this->previousData['idpedido'];
		$idValueNow = $this->idpedido;
		if ($onDeleted || $idValuePrev != $idValueNow) {
			$docModel = new PedidoCliente;
			$this->TotalAdvanceDocs($idDoc, $idValuePrev, $idValueNow, $docModel);
		}

		$idDoc = 'idalbaran';
		$idValuePrev = $this->previousData['idalbaran'];
		$idValueNow = $this->idalbaran;
		if ($onDeleted || $idValuePrev != $idValueNow) {
			$docModel = new AlbaranCliente;
			$this->TotalAdvanceDocs($idDoc, $idValuePrev, $idValueNow, $docModel);
		}

		$idDoc = 'idfactura';
		$idValuePrev = $this->previousData['idfactura'];
		$idValueNow = $this->idfactura;
		if ($onDeleted || $idValuePrev != $idValueNow) {
			$docModel = new FacturaCliente;
			$this->TotalAdvanceDocs($idDoc, $idValuePrev, $idValueNow, $docModel);
		}
	}

	protected function TotalAdvanceDocs($idDoc, $idValuePrev, $idValueNow, $docModel): void
    {
		// Save Total Advance Count
		$anticipoModel = new Anticipo;
			if (isset($idValuePrev) && !is_null($idValuePrev)) {
				$totalAdvanceCountPrev = $anticipoModel->count([new DataBaseWhere($idDoc, $idValuePrev, '=')]);
				$docModel ->loadFromCode($idValuePrev);
				$docModel->advance = $totalAdvanceCountPrev;
				$docModel->save();
			}
			if (isset($idValueNow) && !is_null($idValueNow)) {
				$totalAdvanceCountNow = $anticipoModel->count([new DataBaseWhere($idDoc, $idValueNow, '=')]);
				$docModel->loadFromCode($idValueNow);
				$docModel->advance = $totalAdvanceCountNow;
				$docModel->save();
			}
	}

    public function save(): bool
    {
        // Comprobar que la Empresa y el Cliente del anticipo son el mismo del documento
        if (false === $this->testAnticipoData()) {
            return false;
        }
        if (false === parent::save()) {
            return false;
        }
        return true;
    }

    public function getSubject(): Cliente
    {
        $cliente = new Cliente();
        $cliente->loadFromCode($this->codcliente);
        return $cliente;
    }

    protected function testAnticipoData(): bool
    {

        if ($this->idpresupuesto) {
            if (false === $this->checkAnticipoRelation(new PresupuestoCliente(), $this->idpresupuesto, 'estimation')) {
                return false;
            }
        }

        if ($this->idpedido) {
            if (false === $this->checkAnticipoRelation(new PedidoCliente(), $this->idpedido, 'order')) {
                return false;
            }
        }

        if ($this->idalbaran) {
            if (false === $this->checkAnticipoRelation(new AlbaranCliente(), $this->idalbaran, 'delivery-note')) {
                return false;
            }
        }

        if ($this->idfactura) {
            if (false === $this->checkAnticipoRelation(new FacturaCliente(), $this->idfactura, 'invoice')) {
                return false;
            }
        }

        if (class_exists($this->projectClass) && $this->idproyecto) {
            if (false === $this->checkAnticipoRelation(new $this->projectClass(), $this->idproyecto, 'project')) {
                return false;
            }
        }

        return true;
    }

    protected function checkAnticipoRelation($model, string $code, string $title = ''): bool
    {
        $model->loadFromCode($code);

        // Cuando el anticipo no tiene asignado la Empresa, se le asigna la del documento
        if (empty($this->idempresa)) {
            $this->idempresa = $model->idempresa;
        }

        // Cuando el anticipo no tiene asignado el Cliente, se le asigna el del documento
        if (empty($this->codcliente)) {
            $this->codcliente = $model->codcliente;
        }

        // Comprobar que la Empresa del anticipo es la misma que la empresa del documento
        if ($model->idempresa != $this->idempresa) {
            Tools::Log()->warning('advance-payment-invalid-company-' . $title);
            return false;
        }

        // Comprobar que el Cliente del anticipo es el mismo que el Cliente del documento
        if (!empty($model->codcliente) && $model->codcliente != $this->codcliente) {
            Tools::Log()->warning('advance-payment-invalid-client-' . $title);
            return false;
        }

        return true;
    }

    protected function saveAuditMessage(string $message)
    {
		$this->fecha = Tools::date();
        Tools::log('anticipos')->info($message, [
            '%model%' => $this->modelClassName(),
            '%key%' => $this->primaryColumnValue(),
            '%desc%' => $this->primaryDescription(),
            'model-class' => $this->modelClassName(),
            'model-code' => $this->primaryColumnValue(),
            'model-data' => $this->toArray()
        ]);
    }
}