<?php

namespace Jiangslee\LaravelAliyunSls\Formatters;

use Jiangslee\LaravelAliyunSls\AliyunLogModel\LogItem;
use Monolog\DateTimeImmutable;
use Monolog\Formatter\FormatterInterface;
use Throwable;

class AliyunSlsFormatter implements FormatterInterface
{
    /**
     * 最大前异常深度
     * @var int
     */
    protected $maxPreviousExceptionDepth = 2;


    public function format(array $record)
    {
        /** @var DateTimeImmutable $datetime */
        $datetime = $record['datetime'];

        $result = new LogItem();
        $result->setTime($datetime->getTimestamp());
        $result->setContents([
            'message' => $record['message'],
            'level' => $record['level_name'],
            'env' => $record['channel'],
            'context' => $this->convert($record['context']),
            'extra' => $this->convert($record['extra']),
        ]);

        return $result;
    }

    public function formatBatch(array $records)
    {
        $result = [];
        foreach ($records as $record) {
            $result[] = $this->format($record);
        }
        return $result;
    }

    private function convert(array $data): string
    {
        $result = [];
        foreach ($data as $k => $v) {
            if ($v instanceof Throwable) {
                $result[] = $k . ':'. $this->formatException($v);
            } else {
                $result[] = $k . ':' . json_encode($v, JSON_UNESCAPED_UNICODE);
            }
        }

        return implode(PHP_EOL, $result);
    }

    public function formatException(Throwable $e, bool $isPrevious = false, int $previousDepth = 0): string
    {
        $str = $isPrevious ? "\n[previous exception]" : '';
        $str .= "[{$e->getCode()}] {$e->getMessage()}\n{$e->getFile()}:{$e->getLine()}\n{$e->getTraceAsString()}\n";

        $previous = $e->getPrevious();
        if ($previous instanceof Throwable && $previousDepth < $this->maxPreviousExceptionDepth) {
            $str .= $this->formatException($previous, true, ++$previousDepth);
        }
        return $str;
    }
}
