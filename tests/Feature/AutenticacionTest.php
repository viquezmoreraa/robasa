<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    private function registrar(array $datos = []): TestResponse
    {
        return $this->post('/postulaciones', array_merge([
            'puesto'                => 'chofer',
            'username'              => 'ana_perez',
            'nuevo_correo'          => 'ana@gmail.com',
            'password'              => 'clave1234',
            'password_confirmation' => 'clave1234',
        ], $datos));
    }

    private function crearUsuario(array $datos = []): User
    {
        return User::factory()->create(array_merge([
            'username' => 'ana_perez',
            'email'    => 'ana@gmail.com',
            'password' => Hash::make('clave1234'),
        ], $datos));
    }

    public function test_el_formulario_de_registro_incluye_el_token_csrf(): void
    {
        $this->get('/crear-cuenta?puesto=chofer')
            ->assertOk()
            ->assertSee('name="_token"', false)
            // Las dos contraseñas tienen el botón para mostrarlas
            ->assertSee('aria-controls="password"', false)
            ->assertSee('aria-controls="password_confirmation"', false);
    }

    public function test_el_correo_de_datos_personales_rellena_el_de_la_cuenta(): void
    {
        $this->get('/crear-cuenta?puesto=chofer&correo=ana%40gmail.com')
            ->assertOk()
            ->assertSee('id="nuevo_correo" name="nuevo_correo" value="ana@gmail.com"', false);

        // El usuario lo puede cambiar: se guarda el que escribe en la cuenta, no el de datos personales
        $this->registrar(['correo' => 'ana@gmail.com', 'nuevo_correo' => 'ana.perez@outlook.com']);
        $this->assertSame('ana.perez@outlook.com', User::firstOrFail()->email);
    }

    public function test_el_registro_crea_un_postulante_con_la_contrasena_hasheada(): void
    {
        $this->registrar()->assertRedirect('/exito.html?nuevo_correo=ana%40gmail.com');

        $user = User::where('username', 'ana_perez')->firstOrFail();
        $this->assertSame('ana@gmail.com', $user->email);
        $this->assertSame('postulante', $user->rol);
        $this->assertNotSame('clave1234', $user->password);
        $this->assertTrue(Hash::check('clave1234', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_el_registro_rechaza_dominios_de_correo_no_permitidos(): void
    {
        foreach (['ana@mailinator.com', 'ana@gmail.com.xyz', 'ana@inventado.cr'] as $correo) {
            $this->registrar(['nuevo_correo' => $correo])->assertSessionHasErrors('nuevo_correo');
        }

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_el_registro_no_reutiliza_correos_ni_usuarios_existentes(): void
    {
        $this->crearUsuario();

        // Antes, un correo ya registrado iniciaba sesión en esa cuenta sin pedir su contraseña
        $this->registrar(['username' => 'otra_persona'])->assertSessionHasErrors('nuevo_correo');
        $this->registrar(['nuevo_correo' => 'otra@gmail.com'])->assertSessionHasErrors('username');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_el_registro_exige_una_contrasena_segura_y_confirmada(): void
    {
        $this->registrar(['password' => 'soloLetras', 'password_confirmation' => 'soloLetras'])
            ->assertSessionHasErrors('password');
        $this->assertSame('La contraseña debe incluir al menos un número.', session('errors')->first('password'));

        $this->registrar(['password_confirmation' => 'otraClave99'])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_el_registro_valida_el_formato_de_la_cedula(): void
    {
        foreach (['123', 'abc123456', '0-0234-0567', '1-0234-05678', '1234567890'] as $cedula) {
            $this->registrar(['cedula' => $cedula])->assertSessionHasErrors('cedula');
        }
        $this->assertDatabaseCount('users', 0);

        foreach (['1-0234-0567', '102340567', '155812345678'] as $i => $cedula) {
            $this->registrar(['cedula' => $cedula, 'username' => "ana_{$i}", 'nuevo_correo' => "ana{$i}@gmail.com"])
                ->assertSessionHasNoErrors();
            $this->post('/logout');
        }
        $this->assertDatabaseCount('users', 3);
    }

    public function test_el_login_acepta_correo_o_nombre_de_usuario(): void
    {
        $user = $this->crearUsuario();

        $this->post('/login', ['usuario' => 'ana@gmail.com', 'password' => 'clave1234'])->assertRedirect('/postulante');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout');

        $this->post('/login', ['usuario' => 'ana_perez', 'password' => 'clave1234'])->assertRedirect('/postulante');
        $this->assertAuthenticatedAs($user);
    }

    public function test_el_login_acepta_cuentas_antiguas_sin_username(): void
    {
        // Cuentas creadas antes del cambio: sin username y a veces con correos incompletos
        $user = $this->crearUsuario(['username' => null, 'email' => 'salaz@m']);

        $this->post('/login', ['usuario' => 'salaz@m', 'password' => 'clave1234'])->assertRedirect('/postulante');
        $this->assertAuthenticatedAs($user);
    }

    public function test_el_login_rechaza_credenciales_invalidas_sin_crear_usuarios(): void
    {
        $this->crearUsuario();
        $this->crearUsuario(['username' => 'jose', 'email' => 'jose@robasa.com', 'password' => Hash::make('Jose2026'), 'rol' => 'talento_humano']);

        $this->post('/login', ['usuario' => 'ana_perez', 'password' => 'incorrecta'])->assertSessionHasErrors('usuario');
        $this->post('/login', ['usuario' => 'nuevo', 'password' => 'x'])->assertSessionHasErrors('usuario');
        // La antigua clave maestra ya no sirve con cualquier usuario
        $this->post('/login', ['usuario' => 'cualquiera', 'password' => 'Jose2026'])->assertSessionHasErrors('usuario');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 2);
    }

    public function test_el_login_se_bloquea_tras_cinco_intentos_fallidos(): void
    {
        $this->crearUsuario();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['usuario' => 'ana_perez', 'password' => 'incorrecta']);
        }

        $this->post('/login', ['usuario' => 'ana_perez', 'password' => 'clave1234'])->assertSessionHasErrors('usuario');
        $this->assertStringStartsWith('Demasiados intentos', session('errors')->first('usuario'));
        $this->assertGuest();
    }
}
