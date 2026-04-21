<x-layouts.auth title="Connexion • Scrimly">
    <main class="lg:grid lg:grid-cols-12 bg-bg-main min-h-screen p-6 gap-6 lg:p-0 flex flex-col items-center justify-center">
        <div class="hidden lg:block lg:col-span-7">
            <img src="{{ asset('/img/SejuHextech.jpg') }}" alt="Seju Hextech" class="min-h-screen w-full object-cover">
        </div>
        <div class="lg:col-span-5 w-full max-w-md mx-auto flex flex-col gap-8 lg:pr-6">
            <h1 class="text-[40px] text-gold font-sans font-bold">Connexion</h1>

            <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6 align-start">
                @csrf

                <div class="flex flex-col gap-4">
                    <x-forms.input :name="'email'" :label="'Email'" :type="'email'" :required="true" :placeholder="'john.doe@example.com'" />
                    <x-forms.input :name="'password'" :label="'Mot de passe'" :type="'password'" :required="true" :placeholder="'********'" />
                    <div class="flex flex-row gap-2">
                        <input type="checkbox" name="remember" class="accent-gold">
                        <label class="text-white">
                            Se souvenir de moi
                        </label>
                    </div>
                </div>

                <x-forms.submit class="w-full lg:w-fit lg:self-start">Se connecter</x-forms.submit>
            </form>
            <p class="text-white text-center lg:text-left">
                Pas encore inscrit ? <x-cta href="{{ route('register') }}" class="underline">Inscrivez-vous !</x-cta>
            </p>

        </div>


    </main>
    <footer>

    </footer>
</x-layouts.auth>