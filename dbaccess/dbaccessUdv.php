<?php
interface dbaccessUdv {
    /**
     * @param $preparesql
     * @param null $executesql
     * @return mixed
     */
    public static function prepareExecuteGetStatement($preparesql, $executesql = null);
}