<?php
class Parser{
    
    private $csvInFile = '';
    private $csvOutFile = '';
    private $csvData = array();
    
    public function __construct(string $csvInFile, string $csvOutFile)
    {
        $this->csvInFile = $csvInFile;
        $this->csvOutFile = $csvOutFile;
    }
    
    public function execute(bool $ignoreHeader = false)
    {
        $this->readCsvFile($ignoreHeader);
        
        $this->formatLineData();
        
        $this->joinMultiLineData();
        
        $this->fixColumnData();
        
        $this->writeCsvOutput();
        
    }
    
    protected function fixColumnData()
    {
        foreach ($this->csvData as $loop => $csvDatum)
        {
            $formattedData = $csvDatum;
            
            $formattedData = str_replace('""', '[X]', $formattedData);
            $formattedData = str_replace('"', ' ', $formattedData);
            $formattedData = str_replace('[X]', '"', $formattedData);    
            $length = strlen($formattedData);
            $startQuote = 0;
            for ($start = 0; $start < $length; $start++)
            {
                $characterToCheck = $formattedData[$start];
                if ($characterToCheck == '"')
                {
                    if ($startQuote == 1)
                    {
                        $startQuote = 0;
                    } else {
                        $startQuote = 1;
                    }
                }
                
                if ($startQuote == '1' && $characterToCheck == ',')
                {
                    $formattedData[$start] = ' ';
                }
                
            }
            $this->csvData[$loop] = $formattedData;
        }
    }
    
    
    /**
     * function to write the output of the formatted data into a csv file
     */
    protected function writeCsvOutput()
    {
        $filePointer = fopen($this->csvOutFile, 'w');
        foreach ($this->csvData as $loop => $csvDatum)
        {
            fwrite($filePointer, $csvDatum."\n");
        }
        fclose($filePointer);
    }
    
    /**
     * function to join the multi lines which were split previously
     */
    protected function joinMultiLineData()
    {
        foreach ($this->csvData as $loop => $csvDatum)
        {
            $temporaryArray = explode(',', $csvDatum);
            if (is_numeric($temporaryArray[0]))
            {
                $validLoop = $loop;
            } else {
                $this->csvData[$validLoop] .= ' ' . $csvDatum;
                unset($this->csvData[$loop]);
            }
        }
    }
    
    /**
     * function to remove the quotes and semicolon 
     */
    protected function formatLineData()
    {
        foreach ($this->csvData as $loop => $csvDatum)
        {
            $csvDatum = $this->removeStartingQuote($csvDatum);
            $csvDatum = $this->removeSemicolon($csvDatum);
            $csvDatum = $this->removeEndingQuote($csvDatum);
            $this->csvData[$loop] = $csvDatum;
        }
    }
    
    protected function removeStartingQuote(string $data) : string
    {
        if (substr(trim($data), 0, 1) == '"')
        {
            $data = substr(trim($data), 1);
        }
        
        return $data;
    }
    
    protected function removeSemicolon(string $data) : string
    {
        if (substr(trim($data), -2) == ';;')
        {
            $data = substr(trim($data), 0, (strlen(trim($data)) - 2));
        }
        
        return $data;
    }
    
    protected function removeEndingQuote(string $data) : string
    {
        if (substr(trim($data), -1) == '"')
        {
            if (substr(trim($data), -2) != '""')
            {
                $data = substr(trim($data), 0, (strlen(trim($data)) - 1));
            }
            if (substr(trim($data), -3) == '"""')
            {
                $data = substr(trim($data), 0, (strlen(trim($data)) - 1));
            }
        }
        
        return $data;
    }
    
   
    /**
     * function to read the csv file
     */
    protected function readCsvFile(bool $ignoreHeader = false)
    {
        if (file_exists($this->csvInFile))
        {
            $filePointer = @fopen($this->csvInFile, "r");
            
            if ($filePointer) {
                while (($buffer = fgets($filePointer, 4096)) !== false) 
                {
                    $this->csvData[] = $buffer;
                }
                fclose($filePointer);
                
                if ($ignoreHeader == true)
                {
                    array_shift($this->csvData);
                }
                
            }
        }
    }
    
}