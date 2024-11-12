<?php

namespace Tests\Unit\Services;

use App\Services\GeodataService;
use App\Services\GpxProcessor;
use DateTime;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use MatanYadaev\EloquentSpatial\Objects\LineString;

class GpxProcessorTest extends TestCase
{
    private GpxProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->processor = new GpxProcessor(new GeodataService());
    }

    /** @test */
    public function it_throws_exception_when_file_does_not_exist()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('File does not exist');

        $this->processor->process('nonexistent.gpx');
    }

    /** @test */
    public function it_throws_exception_when_file_is_empty()
    {
        $filePath = Storage::path('empty.gpx');
        file_put_contents($filePath, '');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid GPX file');

        $this->processor->process($filePath);
    }

    /** @test */
    public function it_throws_exception_when_file_has_no_tracks()
    {
        $gpxContent = <<<GPX
<?xml version="1.0" encoding="UTF-8"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="Test">
</gpx>
GPX;

        $filePath = Storage::path('no-tracks.gpx');
        file_put_contents($filePath, $gpxContent);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No tracks found in GPX file');

        $this->processor->process($filePath);
    }

    /** @test */
    public function it_processes_valid_gpx_file_correctly()
    {
        $gpxContent = <<<GPX
<?xml version="1.0" encoding="UTF-8"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="Test">
    <trk>
        <name>Test Track</name>
        <trkseg>
            <trkpt lat="52.2297" lon="21.0122">
                <ele>100</ele>
                <time>2024-01-01T10:00:00Z</time>
            </trkpt>
            <trkpt lat="52.2298" lon="21.0123">
                <ele>101</ele>
                <time>2024-01-01T10:00:10Z</time>
            </trkpt>
        </trkseg>
    </trk>
</gpx>
GPX;

        $filePath = Storage::path('valid.gpx');
        file_put_contents($filePath, $gpxContent);

        $result = $this->processor->process($filePath);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('start_lat', $result);
        $this->assertArrayHasKey('start_lng', $result);
        $this->assertArrayHasKey('end_lat', $result);
        $this->assertArrayHasKey('end_lng', $result);
        $this->assertArrayHasKey('path', $result);
        $this->assertArrayHasKey('distance', $result);

        $this->assertEquals(52.2297, $result['start_lat']);
        $this->assertEquals(21.0122, $result['start_lng']);
        $this->assertEquals(52.2298, $result['end_lat']);
        $this->assertEquals(21.0123, $result['end_lng']);

        $this->assertInstanceOf(LineString::class, $result['path']);

        // Sprawdzamy format danych LineString
        $lineStringArray = $result['path']->toArray();
        $this->assertEquals('LineString', $lineStringArray['type']);
        $this->assertCount(2, $lineStringArray['coordinates']);

        // Sprawdzamy koordynaty (format [longitude, latitude])
        $this->assertEquals([21.0122, 52.2297], $lineStringArray['coordinates'][0]);
        $this->assertEquals([21.0123, 52.2298], $lineStringArray['coordinates'][1]);
    }

    /** @test */
    public function it_handles_multiple_track_segments()
    {
        $gpxContent = <<<GPX
<?xml version="1.0" encoding="UTF-8"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="Test">
    <trk>
        <name>Test Track</name>
        <trkseg>
            <trkpt lat="52.2297" lon="21.0122">
                <ele>100</ele>
                <time>2024-01-01T10:00:00Z</time>
            </trkpt>
        </trkseg>
        <trkseg>
            <trkpt lat="52.2298" lon="21.0123">
                <ele>101</ele>
                <time>2024-01-01T10:00:10Z</time>
            </trkpt>
        </trkseg>
    </trk>
</gpx>
GPX;

        $filePath = Storage::path('multi-segment.gpx');
        file_put_contents($filePath, $gpxContent);

        $result = $this->processor->process($filePath);

        $this->assertInstanceOf(LineString::class, $result['path']);

        $lineStringArray = $result['path']->toArray();
        $this->assertCount(2, $lineStringArray['coordinates']);

        // Sprawdzamy koordynaty (format [longitude, latitude])
        $this->assertEquals([21.0122, 52.2297], $lineStringArray['coordinates'][0]);
        $this->assertEquals([21.0123, 52.2298], $lineStringArray['coordinates'][1]);
    }

    /** @test */
    public function it_calculates_time_and_speed_statistics()
    {
        $gpxContent = <<<GPX
<?xml version="1.0" encoding="UTF-8"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="Test">
    <trk>
        <name>Test Track</name>
        <trkseg>
            <trkpt lat="52.2297" lon="21.0122">
                <ele>100</ele>
                <time>2024-01-01T10:00:00Z</time>
            </trkpt>
            <trkpt lat="52.2298" lon="21.0123">
                <ele>101</ele>
                <time>2024-01-01T10:01:00Z</time>
            </trkpt>
        </trkseg>
    </trk>
</gpx>
GPX;

        $filePath = Storage::path('time-speed.gpx');
        file_put_contents($filePath, $gpxContent);

        $result = $this->processor->process($filePath);

        $this->assertArrayHasKey('duration', $result);
        $this->assertArrayHasKey('avg_speed', $result);
        $this->assertArrayHasKey('max_speed', $result);
        $this->assertArrayHasKey('start_time', $result);
        $this->assertArrayHasKey('end_time', $result);

        $this->assertEquals(60, $result['duration']); // 1 minuta
        $this->assertInstanceOf(DateTime::class, $result['start_time']);
        $this->assertInstanceOf(DateTime::class, $result['end_time']);
    }

    /** @test */
    public function it_calculates_elevation_statistics()
    {
        $gpxContent = <<<GPX
<?xml version="1.0" encoding="UTF-8"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="Test">
    <trk>
        <name>Test Track</name>
        <trkseg>
            <trkpt lat="52.2297" lon="21.0122">
                <ele>100</ele>
                <time>2024-01-01T10:00:00Z</time>
            </trkpt>
            <trkpt lat="52.2298" lon="21.0123">
                <ele>110</ele>
                <time>2024-01-01T10:01:00Z</time>
            </trkpt>
            <trkpt lat="52.2299" lon="21.0124">
                <ele>105</ele>
                <time>2024-01-01T10:02:00Z</time>
            </trkpt>
        </trkseg>
    </trk>
</gpx>
GPX;

        $filePath = Storage::path('elevation.gpx');
        file_put_contents($filePath, $gpxContent);

        $result = $this->processor->process($filePath);

        $this->assertArrayHasKey('elevation_gain', $result);
        $this->assertArrayHasKey('elevation_loss', $result);
        $this->assertArrayHasKey('max_elevation', $result);
        $this->assertArrayHasKey('min_elevation', $result);

        $this->assertEquals(10, $result['elevation_gain']); // Wzrost z 100 na 110
        $this->assertEquals(5, $result['elevation_loss']); // Spadek ze 110 na 105
        $this->assertEquals(110, $result['max_elevation']);
        $this->assertEquals(100, $result['min_elevation']);
    }

    /** @test */
    public function it_handles_missing_time_and_elevation_data()
    {
        $gpxContent = <<<GPX
<?xml version="1.0" encoding="UTF-8"?>
<gpx xmlns="http://www.topografix.com/GPX/1/1" version="1.1" creator="Test">
    <trk>
        <name>Test Track</name>
        <trkseg>
            <trkpt lat="52.2297" lon="21.0122"></trkpt>
            <trkpt lat="52.2298" lon="21.0123"></trkpt>
        </trkseg>
    </trk>
</gpx>
GPX;

        $filePath = Storage::path('missing-data.gpx');
        file_put_contents($filePath, $gpxContent);

        $result = $this->processor->process($filePath);

        $this->assertEquals(0, $result['duration']);
        $this->assertEquals(0, $result['avg_speed']);
        $this->assertEquals(0, $result['max_speed']);
        $this->assertNull($result['start_time']);
        $this->assertNull($result['end_time']);
        $this->assertEquals(0, $result['elevation_gain']);
        $this->assertEquals(0, $result['elevation_loss']);
    }

}
