<?php

namespace Tests\Unit;

use App\Exceptions\InvalidAttendanceQrException;
use App\Models\Employee;
use App\Services\AttendanceQrService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The QR badge is a payroll credential: a scan writes to `attendance`, which
 * feeds accredited hours, which feeds pay. The badge previously encoded a bare
 * employee id, so these assertions exist to keep that from coming back.
 */
class AttendanceQrServiceTest extends TestCase
{
    private AttendanceQrService $qr;

    protected function setUp(): void
    {
        parent::setUp();

        // The project's migrations cannot run on the test connection (see
        // CLAUDE.md), so build only the table these assertions touch.
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
        });

        $this->qr = new AttendanceQrService();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('employees');

        parent::tearDown();
    }

    #[Test]
    public function it_round_trips_a_badge_back_to_its_employee(): void
    {
        $employee = Employee::create(['employee_id' => 'EMP-001', 'first_name' => 'Juan', 'last_name' => 'Dela Cruz']);

        $resolved = $this->qr->resolveEmployee($this->qr->payloadFor($employee));

        $this->assertSame($employee->id, $resolved->id);
    }

    #[Test]
    public function a_bare_employee_id_is_rejected_as_an_unsigned_legacy_card(): void
    {
        $employee = Employee::create(['employee_id' => 'EMP-001', 'first_name' => 'Juan']);

        $this->expectException(InvalidAttendanceQrException::class);
        $this->expectExceptionMessageMatches('/old unsigned QR card/');

        // This is exactly what the old badges encoded, and exactly what someone
        // could generate by hand for any employee they cared to impersonate.
        $this->qr->resolveEmployee((string) $employee->id);
    }

    #[Test]
    public function a_badge_signed_for_one_employee_cannot_be_edited_to_name_another(): void
    {
        $victim = Employee::create(['employee_id' => 'EMP-001', 'first_name' => 'Juan']);
        $attacker = Employee::create(['employee_id' => 'EMP-002', 'first_name' => 'Pedro']);

        // Take the attacker's own valid badge and swap the id for the victim's,
        // keeping the signature — the most obvious forgery to attempt.
        $forged = str_replace(
            AttendanceQrService::VERSION . '.' . $attacker->id . '.',
            AttendanceQrService::VERSION . '.' . $victim->id . '.',
            $this->qr->payloadFor($attacker),
        );

        $this->expectException(InvalidAttendanceQrException::class);

        $this->qr->resolveEmployee($forged);
    }

    #[Test]
    public function a_badge_signed_with_a_different_app_key_is_rejected(): void
    {
        $employee = Employee::create(['employee_id' => 'EMP-001', 'first_name' => 'Juan']);
        $payload = $this->qr->payloadFor($employee);

        Config::set('app.key', 'base64:' . base64_encode(str_repeat('z', 32)));

        $this->expectException(InvalidAttendanceQrException::class);

        (new AttendanceQrService())->resolveEmployee($payload);
    }

    #[Test]
    public function an_unrelated_qr_code_is_not_mistaken_for_a_badge(): void
    {
        $this->assertFalse($this->qr->looksLikeBadge('https://example.com'));
        $this->assertFalse($this->qr->looksLikeBadge('WIFI:S=Lobby;;'));

        $this->expectException(InvalidAttendanceQrException::class);

        $this->qr->resolveEmployee('https://example.com');
    }

    #[Test]
    public function a_well_signed_badge_for_a_deleted_employee_resolves_to_nobody(): void
    {
        $employee = Employee::create(['employee_id' => 'EMP-001', 'first_name' => 'Juan']);
        $payload = $this->qr->payloadFor($employee);
        $employee->delete();

        $this->expectException(InvalidAttendanceQrException::class);
        $this->expectExceptionMessageMatches('/No employee record/');

        $this->qr->resolveEmployee($payload);
    }
}
