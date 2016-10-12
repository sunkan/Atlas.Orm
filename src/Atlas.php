<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm;

use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;
use Exception;

/**
 *
 * An entry point for all Atlas functionality.
 *
 * @package atlas/orm
 *
 */
class Atlas
{
    /**
     *
     * The most recent exception.
     *
     * @var Exception
     *
     */
    protected $exception;

    /**
     *
     * A locator for all Mappers in the system.
     *
     * @var MapperLocator
     *
     */
    protected $mapperLocator;

    /**
     *
     * A prototype transaction.
     *
     * @var Transaction
     *
     */
    protected $prototypeTransaction;

    public function __construct(
        MapperLocator $mapperLocator,
        Transaction $prototypeTransaction
    ) {
        $this->mapperLocator = $mapperLocator;
        $this->prototypeTransaction = $prototypeTransaction;
    }

    /**
     *
     * Returns a Mapper from the locator by its class name.
     *
     * @param string $mapperClass The Mapper class name.
     *
     * @return MapperInterface
     *
     */
    public function mapper($mapperClass)
    {
        return $this->mapperLocator->get($mapperClass);
    }

    /**
     *
     * Returns a new Record from a Mapper.
     *
     * @param string $mapperClass Use this Mapper to create the new Record.
     *
     * @param array $cols Populate the Record with these values.
     *
     * @return RecordInterface
     *
     */
    public function newRecord($mapperClass, array $cols = [])
    {
        return $this->mapper($mapperClass)->newRecord($cols);
    }

    /**
     *
     * Returns a new RecordSet from a Mapper.
     *
     * @param string $mapperClass Use this Mapper to create the new Record.
     *
     * @return RecordSetInterface
     *
     */
    public function newRecordSet($mapperClass)
    {
        return $this->mapper($mapperClass)->newRecordSet();
    }

    /**
     *
     * Fetches one Record by its primary key value from a Mapper, optionally
     * with relateds.
     *
     * @param string $mapperClass Fetch the Record through this Mapper.
     *
     * @param mixed $primaryVal The primary key value; a scalar in the case of
     * simple keys, or an array of key-value pairs for composite keys.
     *
     * @param array $with Return the Record with these relateds stitched in.
     *
     * @return RecordInterface|false A Record on success, or `false` on failure.
     *
     */
    public function fetchRecord($mapperClass, $primaryVal, array $with = [])
    {
        return $this->mapper($mapperClass)->fetchRecord($primaryVal, $with);
    }

    /**
     *
     * Fetches one Record by column-value equality pairs from a Mapper,
     * optionally with relateds.
     *
     * @param string $mapperClass Fetch the Record through this Mapper.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @return RecordInterface|false A Record on success, or `false` on failure.
     *
     */
    public function fetchRecordBy($mapperClass, array $whereEquals, array $with = [])
    {
        return $this->mapper($mapperClass)->fetchRecordBy($whereEquals, $with);
    }

    /**
     *
     * Fetches a RecordSet by primary key values from a Mapper, optionally with
     * relateds.
     *
     * @param string $mapperClass Fetch the RecordSet through this Mapper.
     *
     * @param array $primaryVal The primary key values. Each element in the
     * array is a scalar in the case of simple keys, or an array of key-value
     * pairs for composite keys.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return RecordSetInterface|array A RecordSet on success, or an empty
     * array on failure.
     *
     */
    public function fetchRecordSet($mapperClass, array $primaryVals, array $with = [])
    {
        return $this->mapper($mapperClass)->fetchRecordSet($primaryVals, $with);
    }

    /**
     *
     * Fetches a RecordSet by column-value equality pairs from a Mapper,
     * optionally with relateds.
     *
     * @param string $mapperClass Fetch the RecordSet through this Mapper.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return RecordSetInterface|array A RecordSet on success, or an empty
     * array on failure.
     *
     */
    public function fetchRecordSetBy($mapperClass, array $whereEquals, array $with = [])
    {
        return $this->mapper($mapperClass)->fetchRecordSetBy($whereEquals, $with);
    }

    /**
     *
     * Returns a new select object from a Mapper.
     *
     * @param string $mapperClass Create the select object through this Mapper.
     *
     * @param array $whereEquals A series of column-value equality pairs for the
     * WHERE clause.
     *
     * @return MapperSelect
     *
     */
    public function select($mapperClass, array $whereEquals = [])
    {
        return $this->mapper($mapperClass)->select($whereEquals);
    }

    /**
     *
     * Returns a new Transaction for a unit of work.
     *
     * @return Transaction
     *
     */
    public function newTransaction()
    {
        return clone $this->prototypeTransaction;
    }

    /**
     *
     * Insert a Record through its Mapper as a one-off transaction.
     *
     * @param RecordInterface $record Insert the Row for this Record.
     *
     * @return bool
     *
     */
    public function insert(RecordInterface $record)
    {
        return $this->transact('insert', $record);
    }

    /**
     *
     * Update a Record through its Mapper as a one-off transaction.
     *
     * @param RecordInterface $record Update the Row for this Record.
     *
     * @return bool
     *
     */
    public function update(RecordInterface $record)
    {
        return $this->transact('update', $record);
    }

    /**
     *
     * Delete a Record through its Mapper as a one-off transaction..
     *
     * @param RecordInterface $record Delete the Row for this Record.
     *
     * @return bool
     *
     */
    public function delete(RecordInterface $record)
    {
        return $this->transact('delete', $record);
    }

    /**
     *
     * Returns the most-recent exception from a one-off transaction.
     *
     * @return Exception|null
     *
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     *
     * Performs a one-off transaction.
     *
     * @param string $method The transaction work to perform: insert, update, or
     * delete.
     *
     * @param RecordInterface $record The record to work with.
     *
     * @return bool
     *
     */
    protected function transact($method, RecordInterface $record)
    {
        $this->exception = null;
        $transaction = $this->newTransaction();
        $transaction->$method($record);

        if (! $transaction->exec()) {
            $this->exception = $transaction->getException();
            return false;
        }

        $completed = $transaction->getCompleted();
        $work = $completed[0];
        return $work->getResult();
    }
}
