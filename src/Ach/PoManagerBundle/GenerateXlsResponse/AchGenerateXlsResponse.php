<?php

namespace Ach\PoManagerBundle\GenerateXlsResponse;

class AchGenerateXlsResponse
{
    protected $phpExcelService;

    public function __construct()
    {
	$this->phpExcelService = new \Liuggio\ExcelBundle\Factory();
    }

    /**
    * Generate an simple Excel spreadsheet response out of:
    * 2-dimensional array data contrains the actual data
    * the key values of each row will be used as a header (first row) of the spreadsheet table
    * 
    * @param string
    */
    public function generate($data, $docTitle, $tabTitle)
    {

	$phpExcelObject = $this->phpExcelService->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("ACH")
            ->setLastModifiedBy("ACH")
	    ->setTitle($docTitle);

	$phpExcelObject->setActiveSheetIndex(0);

	$index = 1;
	$letter = 'A';
	/*foreach ($headers as $header)
	{
	    $phpExcelObject->setActiveSheetIndex(0)->setCellValue($letter . '1', $header);
	    $letter++;
	}*/

	// write headers (1st line) of the spreadsheet based on key of actual data
	foreach ($data[0] as $header=>$actual)
	{
	    $phpExcelObject->setActiveSheetIndex(0)->setCellValue($letter . '1', $header);
	    $letter++;
	}

	// write data of the spreadsheet
	foreach ($data as $line)
	{
	    $index++;
	    $letter = 'A';
	    foreach($line as $box)
	    {
		$phpExcelObject->setActiveSheetIndex(0)->setCellValue($letter . strval($index), $box);
		$letter++;
	    }
	}

	$phpExcelObject->getActiveSheet()->setTitle($tabTitle);
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);

        // create the writer
        // $writer = $this->phpExcelService->createWriter($phpExcelObject, 'Excel5');
        $writer = $this->phpExcelService->createWriter($phpExcelObject, 'Excel2007');
        // create the response
        $response = $this->phpExcelService->createStreamedResponse($writer);
        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename=' . $docTitle . '.xlsx');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
	
    }

}