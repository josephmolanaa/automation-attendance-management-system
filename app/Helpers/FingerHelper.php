<?php
namespace App\Helpers;

use Rats\Zkteco\Lib\ZKTeco;

class FingerHelper
{
    public function init($ip, $port = 5005): ZKTeco
    {
        return new ZKTeco($ip, $port);
    }

    public function getStatus(ZKTeco $zk): bool
    {
        return $zk->connect();
    }

    public function getStatusFormatted(ZKTeco $zk): string
    {
        return $zk->connect() ? "Active" : "Deactivate";
    }

    public function getSerial(ZKTeco $zk)
    {
        if ($zk->connect()) {
            $serial = substr(strstr($zk->serialNumber(), '='), 1);
            $zk->disconnect();
            return $serial;
        }
        return false;
    }

    public function testConnection($ip, $port = 5005): array
    {
        try {
            $zk = new ZKTeco($ip, $port);
            $connected = $zk->connect();
            if ($connected) {
                $serial = substr(strstr($zk->serialNumber(), '='), 1);
                $zk->disconnect();
                return ['success' => true, 'serial' => $serial, 'port' => $port];
            }
            return ['success' => false, 'message' => 'Gagal connect ke port ' . $port];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getAttendance(ZKTeco $zk): array
    {
        if ($zk->connect()) {
            $logs = $zk->getAttendance();
            $zk->disconnect();
            return $logs ?: [];
        }
        return [];
    }

    public function disconnect(ZKTeco $zk): void
    {
        $zk->disconnect();
    }
}