<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Console\Command;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;
use function Termwind\render;

final class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Criar um novo administrador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = text(
            label: 'Nome do admin',
            validate: ['name' => 'required|string|max:255']
        );

        $email = text(
            label: 'Email do admin',
            validate: ['email' => ['required', 'lowercase', 'email', 'max:255', Rule::unique(Admin::class, 'email')]]
        );

        $password = password(
            label: 'Qual a senha?',
            placeholder: 'password',
            validate: ['required', Password::defaults()],
            hint: 'Mínimo de 8 caracteres.'
        );

        password(
            label: 'Confirme a senha:',
            placeholder: 'password',
            validate: fn (string $value) => $value !== $password ? 'Senhas não coincidem.' : null
        );

        $admin = Admin::create(compact('name', 'email', 'password'));

        event(new Registered($admin));

        $this->info("Admin {$admin->name} criado com sucesso!");

        $token = $admin->createToken("Token Admin CLI for {$name}", ['admin']);

        $this->infoResult($token->plainTextToken);

        return Command::SUCCESS;
    }

    private function infoResult(string $token): void
    {
        render(<<<HTML
            <div class="text-sky-400">
                <br/>
                The token should be included in the <strong class="text-gray-100">"Authorization"</strong>
                header as a <strong class="text-gray-100">"Bearer"</strong> token:
                <br/>
                <br/>
                <em class="px-1 text-rose-500">{$token}</em>
            </div>
        HTML);
    }
}
