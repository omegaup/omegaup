<?php

namespace OmegaUp;

/**
 * A class that wraps the prometheus metrics.
 */
class Metrics {
    /** @var null|\OmegaUp\Metrics */
    private static $instance = null;

    /** @var \Prometheus\CollectorRegistry */
    private $registry;

    private function __construct() {
        require_once 'libs/third_party/prometheus_client_php/src/Prometheus/Exception/MetricNotFoundException.php';
        require_once 'libs/third_party/prometheus_client_php/src/Prometheus/Storage/Adapter.php';
        require_once 'libs/third_party/prometheus_client_php/src/Prometheus/Collector.php';
        require_once 'libs/third_party/prometheus_client_php/src/Prometheus/CollectorRegistry.php';
        require_once 'libs/third_party/prometheus_client_php/src/Prometheus/Counter.php';
        require_once 'libs/third_party/prometheus_client_php/src/Prometheus/MetricFamilySamples.php';
        require_once 'libs/third_party/prometheus_client_php/src/Prometheus/RenderTextFormat.php';
        require_once 'libs/third_party/prometheus_client_php/src/Prometheus/Sample.php';
        if (function_exists('apcu_clear_cache')) {
            require_once 'libs/third_party/prometheus_client_php/src/Prometheus/Storage/APC.php';
            $adapter = new \Prometheus\Storage\APC();
        } else {
            require_once 'libs/third_party/prometheus_client_php/src/Prometheus/Storage/InMemory.php';
            $adapter = new \Prometheus\Storage\InMemory();
        }
        $this->registry = new \Prometheus\CollectorRegistry($adapter);
    }

    public static function getInstance(): \OmegaUp\Metrics {
        if (is_null(self::$instance)) {
            self::$instance = new \OmegaUp\Metrics();
            return self::$instance;
        }
        return self::$instance;
    }

    public function apiStatus(string $apiName, int $status): void {
        $this
            ->registry
            ->getOrRegisterCounter(
                'frontend',
                'api_request_status_count',
                'status per API request',
                ['api', 'status']
            )
            ->inc([$apiName, $status]);

        $this
            ->registry
            ->getOrRegisterCounter(
                'frontend',
                'api_request_total',
                'API call count',
                ['api']
            )
            ->inc([$apiName]);
    }

    public function getMetrics(): string {
        $renderer = new \Prometheus\RenderTextFormat();
        return $renderer->render($this->registry->getMetricFamilySamples());
    }

    public function render(): void {
        header('Content-type: ' . \Prometheus\RenderTextFormat::MIME_TYPE);
        echo $this->getMetrics();
    }
}
