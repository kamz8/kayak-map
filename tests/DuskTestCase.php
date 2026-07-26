<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Browser;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;

abstract class DuskTestCase extends BaseTestCase
{
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless=new',
            '--no-sandbox',
            '--ignore-certificate-errors',
            '--disable-dev-shm-usage',
            '--window-size=1920,1080',
            '--allow-insecure-localhost',
            '--accept-insecure-certs',
        ]);

        $options->setExperimentalOption('w3c', false);

        // Gdy używasz Selenium w kontenerze
/*        return RemoteWebDriver::create(
            'http://selenium:4444/wd/hub',
            DesiredCapabilities::chrome()
                ->setCapability(ChromeOptions::CAPABILITY, $options)
                ->setCapability('acceptInsecureCerts', true)
        );*/

        // Gdy używasz lokalnego ChromeDriver (zakomentuj powyższe RemoteWebDriver::create i odkomentuj poniższe)

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()
                ->setCapability(ChromeOptions::CAPABILITY, $options)
                ->setCapability('acceptInsecureCerts', true)
        );

    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Use nginx container hostname for Docker environment
        // Chrome in kayak-app container communicates with nginx container
        $baseUrl = env('DUSK_BASE_URL', 'http://nginx');
        $this->app['config']['app.url'] = $baseUrl;

        // Set base URL for Dusk browser
        Browser::$baseUrl = $baseUrl;
    }
}
