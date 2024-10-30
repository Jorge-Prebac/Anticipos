<?php

namespace FacturaScripts\Plugins\Anticipos\Lib\Export;

use FacturaScripts\Core\Tools;
use FacturaScripts\Core\Lib\Email\NewMail;
use FacturaScripts\Core\Lib\Export\PDFExport as MailAnticipos;
use Symfony\Component\HttpFoundation\Response;

class MAILanticiposExport extends MailAnticipos
{
	protected $sendParams = [];

	public function addListModelPage($model, $where, $order, $offset, $columns, $title = ''): bool
    {
		return false;
	}

    public function addModelPage($model, $columns, $title = ''): bool
    {
		$this->sendParams['modelClassName'] = $model->modelClassName();
        $this->sendParams['modelCode'] = $model->primaryColumnValue();
		
        $this->newPage();
        $idempresa = $model->idempresa ?? null;
        $this->insertHeader($idempresa);

        $tableCols = [];
        $tableColsTitle = [];
        $tableOptions = [
            'width' => $this->tableWidth,
            'showHeadings' => 0,
            'shaded' => 0,
            'lineCol' => [1, 1, 1],
            'cols' => []
        ];

        // Get the columns
        $this->setTableColumns($columns, $tableCols, $tableColsTitle, $tableOptions);

        $tableDataAux = [];

		foreach ($tableCols as $key => $colName) {
			$value = $tableOptions['cols'][$key]['widget']->plainText($model);

			if (false !== strpos($colName, 'codcliente')) {
				$colName = Tools::lang()->trans('customer');
			}elseif (false !== strpos($colName, 'codpago')) {
				$colName = Tools::lang()->trans('method-payment');
			}elseif (false !== strpos($colName, 'codproveedor')) {
				$colName = Tools::lang()->trans('supplier');
			}elseif (false !== strpos($colName, 'fase')) {
				$colName = Tools::lang()->trans('advance-linked-to');
			}elseif (false !== strpos($colName, 'fecha')) {
				$colName = Tools::lang()->trans('date');
			}elseif (false !== strpos($colName, 'idalbaran')) {
				$colName = Tools::lang()->trans('delivery-note');
			}elseif (false !== strpos($colName, 'idempresa')) {
				continue;
			}elseif (false !== strpos($colName, 'idfactura')) {
				$colName = Tools::lang()->trans('invoice');
			}elseif (false !== strpos($colName, 'idpedido')) {
				$colName = Tools::lang()->trans('order');
			}elseif (false !== strpos($colName, 'idpresupuesto')) {
				$colName = Tools::lang()->trans('estimation');
			}elseif (false !== strpos($colName, 'idproyecto')) {
				$colName = Tools::lang()->trans('project');
			}elseif (false !== strpos($colName, 'importe')) {
				$colName = Tools::lang()->trans('amount');
			}elseif (false !== strpos($colName, 'nota')) {
				$colName = Tools::lang()->trans('note');
			}elseif (false !== strpos($colName,'riesgomax')) {
				continue;
			}elseif (false !== strpos($colName, 'total')) {
				continue;
			}elseif (false !== strpos($colName, 'nick')) {
				continue;
			}
			$tableDataAux[] = ['key' => $colName, 'value' => $this->fixValue($value)];
		}

        $title .= ': ' . $model->primaryDescription();
        $this->pdf->ezText("\n" . $this->fixValue($title) . "\n", self::FONT_SIZE + 6);
        $this->newLine();

        $this->insertParallelTable($tableDataAux, '', $tableOptions);
        $this->insertFooter();
        return true;
    }
	
	public function show(Response &$response)
	{
		$fileName = $this->getFileName() . '_mail_' . time() . '.pdf';
		$filePath = FS_FOLDER . '/' . NewMail::ATTACHMENTS_TMP_PATH . $fileName;
		if (false === Tools::folderCheckOrCreate(FS_FOLDER . '/' . NewMail::ATTACHMENTS_TMP_PATH) ||
			false === file_put_contents($filePath, $this->getDoc())) {
			Tools::log()->error('folder-not-writable');
			return;
		}

		$this->sendParams['fileName'] = $fileName;
		$response->headers->set('Refresh', '0; SendMail?' . http_build_query($this->sendParams));
	}
}