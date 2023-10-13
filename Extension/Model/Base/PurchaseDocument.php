<?php

namespace FacturaScripts\Plugins\Anticipos\Extension\Model\Base;

use Closure;
use FacturaScripts\Core\Base\DataBase\DataBaseWhere;
use FacturaScripts\Core\Base\ToolBox;
use FacturaScripts\Core\Lib\ReceiptGenerator;
use FacturaScripts\Core\Model\DocTransformation;
use FacturaScripts\Core\Model\EstadoDocumento;
use FacturaScripts\Core\Model\FacturaProveedor;
use FacturaScripts\Core\Model\ReciboProveedor;
use FacturaScripts\Plugins\Anticipos\Model\AnticipoP;

/**
 * Description of PurchaseDocument
 *
 * @property $idestado
 * @method primaryColumnValue()
 * @method primaryColumn()
 * @method modelClassName()
 * @author Juan José Prieto Dzul <juanjoseprieto88@gmail.com>
 * @author Jorge-Prebac              <info@prebac.com>
 */
class PurchaseDocument
{
	public $advance;
	
	public function clear(): Closure
	{
		return function () {
			$this->getAdvance('advance');
			return;
		};
	}

	public function getAdvance(): Closure
	{
		return function ($field) {
			
			$pCl = $this->primaryColumn();
			
			if (false === (empty($this->{$field}))) {
				return;
			}

			$anticipoModel = new AnticipoP();
			$anticipos = $anticipoModel->all([], [], 0, 0);

			if (false === (count($anticipos) != 0)) {
				return;
			}

			$sql = 'UPDATE ' . ($this->tableName()) . ' SET advance = (SELECT COUNT(' . $pCl . ') FROM anticiposp WHERE anticiposp.' . $pCl . ' = ' . ($this->tableName()) . '.' . $pCl . ');';
			
			if (false === (self::$dataBase->exec($sql))) {
				return ($this->toolBox()->i18nLog()->warning('record-save-error'));
			}
		};
	}
}