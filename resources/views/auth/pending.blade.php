<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
        </x-slot>

        <div style="max-width:520px" class="container mt-5 w-100">
          <div class="card p-5 text-center">
              <a href="{{ url('') }}" class="d-flex align-items-center justify-content-center mb-3">
                <!--Logo start-->
                <div class="logo-main">
                    @if(file_exists(base_path("assets/linkstack/images/").findFile('avatar')))
                    <div class="logo-normal">
                      <img class="img logo" src="{{ asset('assets/linkstack/images/'.findFile('avatar')) }}" style="width:auto;height:30px;">
                  </div>
                  <div class="logo-mini">
                    <img class="img logo" src="{{ asset('assets/linkstack/images/'.findFile('avatar')) }}" style="width:auto;height:30px;">
                  </div>
                    @else
                    <div class="logo-normal">
                      <img class="img logo" type="image/svg+xml" src="{{ asset('assets/linkstack/images/logo.svg') }}" width="30px" height="30px">
                  </div>
                  <div class="logo-mini">
                    <img class="img logo" type="image/svg+xml" src="{{ asset('assets/linkstack/images/logo.svg') }}" width="30px" height="30px">
                  </div>
                    @endif
                    </div>
                    <!--logo End-->
                <h4 class="logo-title ms-3">{{env('APP_NAME')}}</h4>
              </a>

              <div style="font-size:2.4rem;margin-top:.5rem;">🚧</div>
              <h2 class="mb-2 mt-2">Thanks for your interest!</h2>
              <p class="mb-4" style="line-height:1.6;">
                  Thanks for showing your interest in becoming an alpha tester.
                  Please visit the Discord for more information.
              </p>

              <a href="https://discord.gg/QCtmW2VNqt" target="_blank" rel="noopener"
                 class="btn btn-primary w-100 mb-3" style="background:#5865F2;border-color:#5865F2;">
                  <i class="bi bi-discord"></i> Join the Discord
              </a>

              <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="btn btn-link text-muted">
                      {{ __('messages.Log out') }}
                  </button>
              </form>
            </div>
          </div>

    </x-auth-card>
</x-guest-layout>
