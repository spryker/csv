<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Library\Reader\Csv;

use SplFileObject;
use Spryker\Shared\Library\Reader\Exception\ResourceNotFoundException;

class CsvReader implements CsvReaderInterface
{

    /**
     * @var
     */
    protected $lineBreaker = "\n";

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var int
     */
    protected $total;

    /**
     * @var int
     */
    protected $readIndex = 1;

    /**
     * @var \SplFileObject
     */
    protected $csvFile;

    /**
     * @var bool
     */
    protected $isScopeReady = false;


    /**
     * @param string $lineBreaker
     */
    public function __construct($lineBreaker = "\n")
    {
        $this->lineBreaker = $lineBreaker;
    }

    /**
     * @param string $filename
     *
     * @return \SplFileObject
     *
     * @throws \Spryker\Shared\Library\Reader\Exception\ResourceNotFoundException
     */
    protected function createCsvFile($filename)
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new ResourceNotFoundException(sprintf(
                'Could not open CSV file "%s"',
                $filename
            ));
        }

        $csvFile = new SplFileObject($filename);
        $csvFile->setCsvControl(',', '"');
        $csvFile->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD);

        return $csvFile;
    }

    /**
     * @return void
     */
    protected function setupScope() //TODO extract to separate class, inject here as dependency, to get rid of state
    {
        if (!$this->isScopeReady) {
            $this->columns = $this->csvFile->fgetcsv();

            $this->total = null;
            $this->readIndex = 1;
            $this->isScopeReady = true;
        }
    }

    /**
     * @param array $columns
     * @param array $data
     *
     * @return array
     */
    public function composeItem(array $columns, array $data)
    {
        $data = array_values($data);
        $columns = array_values($columns);
        $columnCount = count($columns);
        $dataCount = count($data);

        if ($columnCount !== $dataCount) {
            throw new \UnexpectedValueException(sprintf(
                'Expected %d column(s) but received data with %d column(s)',
                $columnCount,
                $dataCount
            ));
        }

        return array_combine($columns, $data);
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        $this->setupScope();

        return $this->columns;
    }

    /**
     * @return \SplFileObject
     *
     * @throws \LogicException
     */
    public function getFile()
    {
        $this->setupScope();

        return $this->csvFile;
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        if ($this->total === null) {
            $csvFile = $this->createCsvFile($this->getFile()->getPathname());
            $csvFile->rewind();

            $lines = 1;
            while (!$csvFile->eof()) {
                $lines += substr_count($csvFile->fread(8192), $this->lineBreaker);
            }

            $this->total = $lines;
        }

        return $this->total;
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return $this->csvFile !== null && $this->csvFile->isReadable();
    }

    /**
     * @param string $filename
     *
     * @throws \Spryker\Shared\Library\Reader\Exception\ResourceNotFoundException
     *
     * @return $this
     */
    public function load($filename)
    {
        $this->isScopeReady = false;
        $this->csvFile = $this->createCsvFile($filename);

        $this->setupScope();

        return $this;
    }

    /**
     * @throws \Spryker\Shared\Library\Reader\Exception\ResourceNotFoundException
     *
     * @return array
     */
    public function read()
    {
        $data = $this->getFile()->fgetcsv();
        if (empty($data)) {
            throw new ResourceNotFoundException('Malformed data at line ' . $this->readIndex);
        }

        $data = $this->composeItem($this->columns, $data);
        $this->readIndex++;

        return $data;
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return $this->getFile()->eof();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [];
        $csvFile = $this->createCsvFile($this->getFile()->getPathname());
        $csvFile->rewind();

        while (!$csvFile->eof()) {
            $data[] = $this->composeItem($this->columns, $csvFile->fgetcsv());
        }

        return $data;
    }

}
