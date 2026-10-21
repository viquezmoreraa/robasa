<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\RestablecerContrasena;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class RecuperarContrasenaTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(): User
    {
        return User::factory()->create([
            'username' => 'ana_perez',
            'email'    => 'ana@gmail.com',
            'password' => Hash::make('clave1234'),
        ]);
    }

    private function restablecer(string $token, array $datos = [])
    {
        return $this->post('/restablecer-contrasena', array_merge([
            'token'                 => $token,
            'email'                 => 'ana@gmail.com',
            'password'              => 'nueva5678',
            'password_confirmation' => 'nueva5678',
        ], $datos));
    }

    public function test_el_login_muestra_el_ojo_y_el_enlace_de_recuperacion(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('class="toggle-password"', false)
            ->assertSee('¿Olvidó su contraseña?')
            ->assertSee(route('password.request'), false);
    }

    public function test_envia_el_enlace_al_correo_registrado(): void
    {
        Notification::fake();
        $user = $this->crearUsuario();

        $this->from('/olvide-contrasena')->post('/olvide-contrasena', ['email' => 'ana@gmail.com'])
            ->assertRedirect('/olvide-contrasena')
            ->assertSessionHas('ok');

        Notification::assertSentTo($user, RestablecerContrasena::class, function ($notificacion) use ($user) {
            $correo = $notificacion->toMail($user);

            return $notificacion->locale === 'es'
                && str_contains($correo->actionUrl, '/restablecer-contrasena/')
                && str_contains($correo->actionUrl, 'email=ana%40gmail.com');
        });
        // En la base solo queda el hash del token, nunca el token en claro
        $this->assertDatabaseCount('password_reset_tokens', 1);
    }

    public function test_responde_igual_si_el_correo_no_existe(): void
    {
        Notification::fake();
        $this->crearUsuario();

        $existe = $this->post('/olvide-contrasena', ['email' => 'ana@gmail.com']);
        $noExiste = $this->post('/olvide-contrasena', ['email' => 'nadie@gmail.com']);

        $this->assertSame($existe->getSession()->get('ok'), $noExiste->getSession()->get('ok'));
        $noExiste->assertSessionHasNoErrors();
        Notification::assertSentTimes(RestablecerContrasena::class, 1);
    }

    public function test_el_token_valido_cambia_la_contrasena_una_sola_vez(): void
    {
        $user = $this->crearUsuario();
        $token = Password::createToken($user);

        $this->get("/restablecer-contrasena/{$token}?email=ana%40gmail.com")
            ->assertOk()
            ->assertSee('value="ana@gmail.com"', false);

        $this->restablecer($token)->assertRedirect('/login')->assertSessionHas('ok');
        $this->assertTrue(Hash::check('nueva5678', $user->fresh()->password));
        $this->assertDatabaseCount('password_reset_tokens', 0);

        // El mismo enlace ya no sirve
        $this->restablecer($token, ['password' => 'otra9999', 'password_confirmation' => 'otra9999'])
            ->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('nueva5678', $user->fresh()->password));

        $this->post('/login', ['usuario' => 'ana_perez', 'password' => 'nueva5678'])->assertRedirect('/postulante');
    }

    public function test_el_token_vence_a_los_30_minutos(): void
    {
        $user = $this->crearUsuario();
        $token = Password::createToken($user);

        $this->travel(31)->minutes();

        $this->restablecer($token)->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check('clave1234', $user->fresh()->password));
    }

    public function test_rechaza_tokens_inventados_o_de_otro_correo(): void
    {
        $user = $this->crearUsuario();
        User::factory()->create(['email' => 'luis@gmail.com']);
        $token = Password::createToken($user);

        $this->restablecer('token-inventado')->assertSessionHasErrors('email');
        $this->restablecer($token, ['email' => 'luis@gmail.com'])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('clave1234', $user->fresh()->password));
    }

    public function test_la_contrasena_nueva_debe_ser_segura_y_confirmada(): void
    {
        $user = $this->crearUsuario();
        $token = Password::createToken($user);

        $this->restablecer($token, ['password' => 'soloLetras', 'password_confirmation' => 'soloLetras'])
            ->assertSessionHasErrors('password');
        $this->restablecer($token, ['password_confirmation' => 'distinta99'])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('clave1234', $user->fresh()->password));
    }
}
