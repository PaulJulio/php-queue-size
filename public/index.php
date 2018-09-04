<?php
ini_set('memory_limit', -1);
require_once __DIR__ . '/../vendor/autoload.php';

$schedule = new \QueueSize\Schedule();

$item = new \QueueSize\Schedule\Item();
$item->setTicks(3600);
$item->setStatus(\QueueSize\Schedule\Item::STATUS_OFFLINE);
$item->setRateIncoming(4);
$item->setCostOutgoing(10);
$item->setIdle(true);
$schedule->addScheduleItem($item);

$item = new \QueueSize\Schedule\Item();
$item->setTicks(30);
$item->setStatus(\QueueSize\Schedule\Item::STATUS_ONLINE);
$item->setRateIncoming(4);
$item->setCostOutgoing(.4);
$item->setIdle(true);
$schedule->addScheduleItem($item);

$simulator = new \QueueSize\Simulator($schedule);
$simulator->addWorkers(3);
$simulator->simulate();

$delivered = $simulator->getDelivered();
$purged = $simulator->getPurged();

echo "<table><tr><td>#</td><td>Created</td><td>Delivered</td><td>Retries</td></tr>";
$count = 0;
/* \QueueSize\Notification $notification */
foreach ($delivered as $notification) {
    echo strtr('<tr><td>{count}</td><td>{created}</td><td>{delivered}</td><td>{retries}</td></tr>', [
        '{count}' => ++$count,
        '{created}' => $notification->getTickCreate(),
        '{delivered}' => $notification->getTickDeliver(),
        '{retries}' => $notification->getRetries(),
    ]);
}
echo "</table>";

$count = 0;
echo "<table><tr><td>#</td><td>Created</td><td>Delivered</td><td>Retries</td><td>Lock</td></tr>";
/* \QueueSize\Notification $notification */
foreach ($purged as $notification) {
    echo strtr('<tr><td>{count}</td><td>{created}</td><td>{delivered}</td><td>{retries}</td><td>{lock}</td></tr>', [
        '{count}' => ++$count,
        '{created}' => $notification->getTickCreate(),
        '{delivered}' => $notification->getTickDeliver(),
        '{retries}' => $notification->getRetries(),
        '{lock}' => $notification->getTickLock(),
    ]);
}
echo "</table>";
