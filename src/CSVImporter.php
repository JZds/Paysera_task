<?php

namespace JZds\Paysera_task;


class CSVImporter {

    private $file;
    private $rowLength = 8000;
    private $delimiter = ",";
    private $header = array("date","user_id","user_type","operation","amount","currency");
    private $parseHeader = true;

    public function importData($filename, $max_lines){

        if ( !is_readable($filename)) {
            echo "No such file: $filename \n";
            die();
        } elseif(filesize($filename) == 0 ){
            echo "File is empty: $filename \n";
            die();
        }
        $this->file = fopen("$filename", "r");

        $data = array();

        if ($max_lines > 0)
            $line_count = 0;
        else
            $line_count = -1;

        while ($line_count < $max_lines && ($row = fgetcsv($this->file, $this->rowLength, $this->delimiter)) !== FALSE)
        {
            if ($this->parseHeader)
            {
                foreach ($this->header as $i => $heading_i)
                {
                    $row_new[$heading_i] = $row[$i];
                }
                $data[] = $row_new;
            }
            else
            {
                $data[] = $row;
            }
            if ($max_lines > 0)
                $line_count++;
        }
        if ($this->file)
        {
            fclose($this->file);
        }
        return $data;
    }
}