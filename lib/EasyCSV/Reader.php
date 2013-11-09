<?php

namespace EasyCSV;

class Reader extends AbstractBase
{
    protected $headersInFirstRow = true;
    protected $headers;
    protected $line;
    protected $init;

    public function __construct($path, $mode = 'r+', $headersInFirstRow = true)
    {
        if ('content' === $mode) {
            $fh = fopen('php://temp/maxmemory:' . (5 * 1024 * 1024), 'rw');
            fwrite($fh, $path);
            rewind($fh);
            $this->handle = $fh;
        } elseif (is_string($path)) {
            parent::__construct($path, $mode);
        } elseif ('stream' === get_resource_type($path)) {
            $this->handle = $path;
        }

        $this->headersInFirstRow = $headersInFirstRow;
        $this->line = 0;
    }

    public function getHeaders()
    {
        $this->init();
        return $this->headers;
    }

    public function getRow()
    {
        $this->init();
        if (($row = fgetcsv($this->handle, 1000, $this->delimiter, $this->enclosure)) !== false) {
            $this->line++;
            return $this->headers ? array_combine($this->headers, $row) : $row;
        } else {
            return false;
        }
    }

    public function getAll()
    {
        $data = array();
        while ($row = $this->getRow()) {
            $data[] = $row;
        }
        return $data;
    }

    public function getLineNumber()
    {
        return $this->line;
    }

    protected function init()
    {
        if (true === $this->init) {
            return;
        }
        $this->init    = true;
        $this->headers = $this->headersInFirstRow === true ? $this->getRow() : false;
    }
}
