<?php namespace OrmExtension\Migration;

class ColumnTypes {
    const INT               = 'INT';
    const INT_NOT_NULL      = 'INT NOT NULL';
    const INT_NULL          = 'INT';
    const DECIMAL           = 'DECIMAL(10,4) NOT NULL';
    const FLOAT             = 'FLOAT NOT NULL';
    const DOUBLE            = 'DOUBLE NOT NULL';
    const VARCHAR_4095_NULL = 'VARCHAR(4095)';
    const VARCHAR_4095      = 'VARCHAR(4095) NOT NULL';
    const VARCHAR_2047      = 'VARCHAR(2047) NOT NULL';
    const VARCHAR_1023      = 'VARCHAR(1023) NOT NULL';
    const VARCHAR_1023_NULL = 'VARCHAR(1023)';
    const VARCHAR_511       = 'VARCHAR(511) NOT NULL';
    const VARCHAR_255       = 'VARCHAR(255) NOT NULL';
    const VARCHAR_127       = 'VARCHAR(127) NOT NULL';
    const VARCHAR_63        = 'VARCHAR(63) NOT NULL';
    const VARCHAR_27        = 'VARCHAR(27) NOT NULL';
    const CREATED           = 'DATETIME';
    const DATETIME          = 'DATETIME';
    const TIMESTAMP         = 'TIMESTAMP NOT NULL';
    const TIME              = 'TIME NOT NULL';
    const TEXT              = 'TEXT NOT NULL';
    const MEDIUMTEXT        = 'MEDIUMTEXT NOT NULL';
    const BOOL_0            = 'BOOLEAN NOT NULL DEFAULT FALSE';
    const BOOL_1            = 'BOOLEAN NOT NULL DEFAULT TRUE';
}
