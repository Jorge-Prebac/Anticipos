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

namespace FacturaScripts\Plugins\Anticipos\Model;

use FacturaScripts\Core\Plugins;
use FacturaScripts\Core\Session;
use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Template\ModelClass;
use FacturaScripts\Core\Template\ModelTrait;
use FacturaScripts\Dinamic\Model\AlbaranProveedor;
use FacturaScripts\Dinamic\Model\Proveedor;
use FacturaScripts\Dinamic\Model\FacturaProveedor;
use FacturaScripts\Dinamic\Model\PedidoProveedor;
use FacturaScripts\Dinamic\Model\PresupuestoProveedor;
use FacturaScripts\Dinamic\Model\Proyecto;

/**
 * Description of AnticipoP
 *
 * @autor Jorge-Prebac                         <info@smartcuines.com>
 * @autor Daniel Fernández Giménez <hola@danielfg.es>
 * @autor Juan José Prieto Dzul           <juanjoseprieto88@gmail.com>
 */
class AnticipoP extends ModelClass
{
    use ModelTrait;

    /** @return string */
    public $coddivisa;

    /** @return string */
	public $codpago;

    /** @return string */
    public $codproveedor;

    /** @return string */
    public $fase;

    /** @return string */
    public $fecha;

    /** @var integer */
    public $id;

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
	public $nick;

    /** @return string */
    public $nota;

    public function __get(string $key)
    {
		if (isset($this->attributes[$key])) {
			return $this->attributes[$key];
        }
        switch ($key) {
            case 'totaldelivery':
                $delivery = new AlbaranProveedor();
                $delivery->load($this->idalbaran);
                return $delivery->total;
            case 'totalestimation':
                $estimation = new PresupuestoProveedor();
                $estimation->load($this->idpresupuesto);
                return $estimation->total;
            case 'totalinvoice':
                $invoice = new FacturaProveedor();
                $invoice->load($this->idfactura);
                return $invoice->total;
            case 'totalorder':
                $order = new PedidoProveedor();
                $order->load($this->idpedido);
                return $order->total;
            case 'totalproject':
				if (Plugins::isEnabled('Proyectos')) {
                    $project = new Proyecto();
                    $project->load($this->idproyecto);
                    return $project->totalcompras;
                }
                return 0;
			case 'riesgomax':
				return $this->getSubject()->riesgomax;
			case 'totalrisk':
				return $this->getSubject()->riesgoalcanzado;
		}
		return null;
	}

    public function __isset(string $key): bool
    {
        if (isset($this->attributes[$key])) {
            return true;
        }
		switch ($key) {
			case 'totaldelivery':
			case 'totalestimation':
			case 'totalinvoice':
			case 'totalorder':
			case 'totalproject':
			case 'riesgomax':
			case 'totalrisk':
				return true;
		}
        return false;
    }

    public function clear(): void
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
        return 'anticiposp';
    }

	protected function setPreviousData(array $fields = [])
    {
        $docs = ['idpresupuesto', 'idpedido', 'idalbaran', 'idfactura'];
		parent::setPreviousData(array_merge($docs, $fields));
    }

	protected function onInsert(): void
    {
		$onDeleted = false;

		// Save Total Advance Count
		$this->AdvanceData($onDeleted);

		// Save audit log
		$this->saveAuditMessage('inserted-model');

        parent::onInsert();
    }

    protected function onUpdate(): void
    {
		$onDeleted = false;

		//Save Total Advance Count
		$this->AdvanceData($onDeleted);

		// Save audit log
		$this->saveAuditMessage('updated-model');

        parent::onUpdate();
    }

	protected function onDelete(): void
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
		$idValuePrev = $this->getOriginal('idpresupuesto');
		$idValueNow = $this->idpresupuesto;
		if ($onDeleted || $idValuePrev != $idValueNow) {	
			$docModel = new PresupuestoProveedor;
			$this->TotalAdvanceDocs($idDoc, $idValuePrev, $idValueNow, $docModel);
		}

		$idDoc = 'idpedido';
		$idValuePrev = $this->getOriginal('idpedido');
		$idValueNow = $this->idpedido;
		if ($onDeleted || $idValuePrev != $idValueNow) {
			$docModel = new PedidoProveedor;
			$this->TotalAdvanceDocs($idDoc, $idValuePrev, $idValueNow, $docModel);
		}

		$idDoc = 'idalbaran';
		$idValuePrev = $this->getOriginal('idalbaran');
		$idValueNow = $this->idalbaran;
		if ($onDeleted || $idValuePrev != $idValueNow) {
			$docModel = new AlbaranProveedor;
			$this->TotalAdvanceDocs($idDoc, $idValuePrev, $idValueNow, $docModel);
		}

		$idDoc = 'idfactura';
		$idValuePrev = $this->getOriginal('idfactura');
		$idValueNow = $this->idfactura;
		if ($onDeleted || $idValuePrev != $idValueNow) {
			$docModel = new FacturaProveedor;
			$this->TotalAdvanceDocs($idDoc, $idValuePrev, $idValueNow, $docModel);
		}
	}

	protected function TotalAdvanceDocs($idDoc, $idValuePrev, $idValueNow, $docModel): void
    {
		// Save Total Advance Count
		$anticipoModel = new AnticipoP;
			if (isset($idValuePrev) && !is_null($idValuePrev)) {
				$totalAdvanceCountPrev = $anticipoModel->count([new DataBaseWhere($idDoc, $idValuePrev, '=')]);
				$docModel ->load($idValuePrev);
				$docModel->advance = $totalAdvanceCountPrev;
				$docModel->save();
			}
			if (isset($idValueNow) && !is_null($idValueNow)) {
				$totalAdvanceCountNow = $anticipoModel->count([new DataBaseWhere($idDoc, $idValueNow, '=')]);
				$docModel->load($idValueNow);
				$docModel->advance = $totalAdvanceCountNow;
				$docModel->save();
			}
	}

    public function save(): bool
    {
        // Comprobar que la Empresa y el Proveedor del anticipo son el mismo del documento
        if (false === $this->testAnticipoData()) {
            return false;
        }
        if (false === parent::save()) {
            return false;
        }
        return true;
    }

    public function getSubject(): Proveedor
    {
        $proveedor = new Proveedor();
        $proveedor->load($this->codproveedor);
        return $proveedor;
    }

    protected function testAnticipoData(): bool
    {

        if ($this->idpresupuesto) {
            if (false === $this->checkAnticipoRelation(new PresupuestoProveedor(), $this->idpresupuesto, 'estimation')) {
                return false;
            }
        }

        if ($this->idpedido) {
            if (false === $this->checkAnticipoRelation(new PedidoProveedor(), $this->idpedido, 'order')) {
                return false;
            }
        }

        if ($this->idalbaran) {
            if (false === $this->checkAnticipoRelation(new AlbaranProveedor(), $this->idalbaran, 'delivery-note')) {
                return false;
            }
        }

        if ($this->idfactura) {
            if (false === $this->checkAnticipoRelation(new FacturaProveedor(), $this->idfactura, 'invoice')) {
                return false;
            }
        }

        if (Plugins::isEnabled('Proyectos') && $this->idproyecto) {
            if (false === $this->checkAnticipoRelation(new $this->Proyecto(), $this->idproyecto, 'project')) {
                return false;
            }
        }

        return true;
    }

    protected function checkAnticipoRelation($model, string $code, string $title = ''): bool
    {
        $model->load($code);

        // Cuando el anticipo no tiene asignado la Empresa, se le asigna la del documento
        if (empty($this->idempresa)) {
            $this->idempresa = $model->idempresa;
        }

        // Cuando el anticipo no tiene asignado el Proveedor, se le asigna el del documento
        if (empty($this->codproveedor) && $title != "project") {
            $this->codproveedor = $model->codproveedor;
        }

        // Comprobar que la Empresa del anticipo es la misma que la empresa del documento
        if ($model->idempresa != $this->idempresa) {
            Tools::Log()->warning('advance-payment-invalid-company-' . $title);
            return false;
        }

        // Comprobar que el Proveedor del anticipo es el mismo que el Proveedor del documento
        if (!empty($model->codproveedor) && $model->codproveedor != $this->codproveedor) {
            Tools::Log()->warning('advance-payment-invalid-supplier-' . $title);
            return false;
        }

        return true;
    }

    protected function saveAuditMessage(string $message)
    {
		$this->fecha = Tools::date();
        Tools::log('anticipos')->info($message, [
            '%model%' => $this->modelClassName(),
            '%key%' => $this->id(),
            '%desc%' => $this->primaryDescription(),
            'model-class' => $this->modelClassName(),
            'model-code' => $this->id(),
            'model-data' => $this->getDirty()
        ]);
    }
}