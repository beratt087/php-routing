<?php

namespace Luadex\Core;

class Database {
	public \PDO          $db;
	public static string $table;
	public array         $where = [];
	protected string     $sql   = '';
	public static string $queryFormat;
	public array         $data  = [];

	public function __construct() {
		$dsn = sprintf("mysql:host=%s;dbname=%s;charset=%s", $_ENV['DB_HOST'], $_ENV['DB_NAME'], $_ENV['DB_CHARSET']);
		try {
			$this->db = new \PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
		} catch (\PDOException $err) {
			if ($err) throw $err;
		}
	}

	public static function table(string $name) {
		self::$table = $name;
		return new self();
	}

	public function where($column, $value, $operation = '=') {
		$this->where[] = $column . ' ' . $operation . ' "' . $value . '"';
		return $this;
	}

	protected function prepareSql() {
		$queryFormat = self::$queryFormat;
		switch ($queryFormat) {
			case 'select':
				$this->sql = sprintf('SELECT * FROM %s', self::$table);
				if (count($this->where)) {
					$this->sql .= ' WHERE ' . implode(' && ', $this->where);
				}
				break;
			case 'update':
				$this->sql = sprintf('UPDATE %s SET', self::$table);
				if (count($this->data)) {
					foreach ($this->data as $key => $value) {
						if (array_key_last($this->data) == $key) {
							$this->sql .= ' ' . $key . ' = :' . $key;
						}
						else {
							$this->sql .= ' ' . $key . ' = :' . $key . ', ';
						}
					}
					if (count($this->where)) {
						$this->sql .= ' WHERE ' . implode(' && ', $this->where);
					}
				}
				break;
			case 'insert':
				$this->sql   = sprintf("INSERT INTO %s (", self::$table);
				$valueString = "";
				foreach ($this->data as $key => $value) {
					if (array_key_last($this->data) == $key) {
						$valueString .= "?";
						$this->sql   .= $key . ')';
					}
					else {
						$this->sql   .= $key . ', ';
						$valueString .= "?, ";
					}
				}
				$this->sql .= " VALUES(" . $valueString . ")";
				break;
			default:
				break;
		}
	}

	/**
	 * @return mixed
	 */
	public function get(): mixed {
		self::$queryFormat = 'select';
		$this->prepareSql();
		$query = $this->db->prepare($this->sql);
		$query->execute();
		return $query->fetchAll(\PDO::FETCH_OBJ);
	}

	/**
	 * @return mixed
	 */
	public function first(): mixed {
		self::$queryFormat = 'select';
		$this->prepareSql();
		$query = $this->db->prepare($this->sql);
		$query->execute();
		return $query->fetch(\PDO::FETCH_OBJ);
	}

	/**
	 * @return int
	 */
	public function update($values): int {
		self::$queryFormat = 'update';
		$this->data        = $values;
		$this->prepareSql();
		$query = $this->db->prepare($this->sql);
		$query->execute($this->data);
		return $query->rowCount();
	}

	public function insert($values): bool {
		self::$queryFormat = 'insert';
		$this->data        = $values;
		$this->prepareSql();
		$query = $this->db->prepare($this->sql);
		$query->execute(array_values($this->data));
		return $query->execute();
	}
}