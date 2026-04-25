<script lang="ts">
  import '../../styles/app.scss';
  import { onMount } from 'svelte';
  import Login from './Login.svelte';
  import Register from './Register.svelte';
  import { goto } from '$app/navigation';

  let username = $state('');
  let userpic = $state('');

  onMount(() => {
    username = sessionStorage.getItem('USERNAME') ?? '';
    userpic =  sessionStorage.getItem('USERPIC') ?? '';        
  });  

async function logout(e: any) {
    e.preventDefault();
    sessionStorage.clear();
    await goto('/', { replaceState: true });
    window.location.reload();
}


</script>
<div>
<nav class="navbar navbar-expand-lg menu-gradient">
  <div class="container-fluid">
    <a class="navbar-brand" href="/"><img class="logo" src="/images/logo.png" alt=""/></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-controls="navbarSupportedContent" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a data-sveltekit-preload-data="tap" class="nav-link emboss-menu" aria-current="page" href="/about">About Us</a>
        </li>
        <li class="nav-item dropdown">
          <div data-sveltekit-preload-data="tap" class="nav-link dropdown-toggle emboss-menu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Products
          </div>
          <ul class="dropdown-menu">
            <li><a data-sveltekit-preload-data="tap" class="dropdown-item" href="/list">Products List</a></li>
            <li><a data-sveltekit-preload-data="tap" class="dropdown-item" href="/catalog">Products Catalog</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a data-sveltekit-preload-data="tap" class="dropdown-item" href="/search">Products Search</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a data-sveltekit-preload-data="tap" class="nav-link emboss-menu" aria-disabled="true" href="/contact">Contact Us</a>
        </li>
      </ul>
      {#if username !== ''}

      <ul class="navbar-nav mr-auto">
        <li class="nav-item dropdown">
          <div class="nav-link dropdown-toggle active" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <img class="user" src={userpic as string} alt="" />&nbsp;<span class="emboss-menu">{username as string}</span>
          </div>
          <ul class="dropdown-menu">
            <li>
              <button onclick={logout} class="dropdown-item cursor font12">Log-Out</button>
          </li>
            <li>
              <a class="dropdown-item" href="/profile">Profile</a>                
          </li>
            <li><hr class="dropdown-divider border-gray"/></li>
            <li>
              <a class="dropdown-item" href="/#">Messenger</a>
          </li>
          </ul>
        </li>              
      </ul> 

      {:else}
        <ul class="navbar-nav mr-auto">
          <li class="nav-item">
            <button id="login" data-sveltekit-preload-data="tap" class="nav-link emboss-menu  cursor" aria-disabled="true" data-bs-toggle="modal" data-bs-target="#staticLogin">Login</button>
          </li>
          <li class="nav-item">
            <button id="register" data-sveltekit-preload-data="tap" class="nav-link emboss-menu cursor" aria-disabled="true" data-bs-toggle="modal" data-bs-target="#staticRegister">Register</button>
          </li>
        </ul>
      {/if}
    </div>
  </div>
</nav>

<div class="offcanvas offcanvas-end" data-bs-scroll="true" id="offcanvasMenu" aria-labelledby="offcanvasWithBothOptionsLabel">
  <div class="offcanvas-header bg-primary">
    <h5 class="offcanvas-title text-white" id="offcanvasWithBothOptionsLabel">Drawer Menu</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">

    <ul class="nav flex-column">
      <li class="nav-item" data-bs-dismiss="offcanvas">
        <a class="nav-link emboss-menu" aria-current="page" href="/about">About Us</a>
      </li>
      <li class="nav-item"><hr/></li>
      <li class="nav-item dropdown">
            <div data-sveltekit-preload-data="tap" class="nav-link dropdown-toggle emboss-menu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Products
            </div>
            <ul class="dropdown-menu">
              <li data-bs-dismiss="offcanvas"><a data-sveltekit-preload-data="tap" class="dropdown-item" href="/list">Products List</a></li>
              <li><hr class="dropdown-divider"/></li>
              <li data-bs-dismiss="offcanvas"><a data-sveltekit-preload-data="tap" class="dropdown-item" href="/catalog">Products Catalog</a></li>
              <li><hr class="dropdown-divider"/></li>
              <li data-bs-dismiss="offcanvas"><a data-sveltekit-preload-data="tap" class="dropdown-item" href="/search">Product Search</a></li>
            </ul>
        </li>

        <li class="nav-item"><hr/></li>
        <li class="nav-item" data-bs-dismiss="offcanvas">
          <a data-sveltekit-preload-data="tap" class="nav-link emboss-menu" aria-current="page" href="/contact">Contact Us</a>
        </li>
        <li class="nav-item"><hr/></li>

        </ul>
        {#if username !== ''}
        <ul class="navbar-nav mr-auto">              
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle active" href="/#" role="button" data-bs-toggle="dropdown" aria-expanded="false">                   
              <img class="user" src={userpic as string} alt="" />&nbsp;<span class="emboss-menu">{username as string}</span>                   
           </a>
            <ul class="dropdown-menu">
              <li data-bs-dismiss="offcanvas">
                <button type="button" onclick={logout} class="dropdown-item font12" >LogOut</button>
              </li>
              <li class="nav-item"><hr/></li>
              <li data-bs-dismiss="offcanvas">
                <a class="dropdown-item" href="/profile">Profile</a> 
              </li>
              <li><hr class="dropdown-divider"/></li>
              <li data-bs-dismiss="offcanvas">
                <a class="dropdown-item" href="/#">Messenger</a>
              </li>
            </ul>
          </li>          
          <li class="nav-item"><hr/></li>                                        
        </ul>                    

          {:else}
            <ul class="nav flex-column">
              <li class="nav-item" data-bs-dismiss="offcanvas">
                <button data-sveltekit-preload-data="tap" class="nav-link cursor emboss-menu" data-bs-toggle="modal" data-bs-target="#staticLogin">Login</button>
              </li>
              <li class="nav-item"><hr/></li>
              <li class="nav-item" data-bs-dismiss="offcanvas">
                <button data-sveltekit-preload-data="tap" class="nav-link cursor emboss-menu" data-bs-toggle="modal" data-bs-target="#staticRegister">Register</button>
              </li>            
            </ul>
          
            {/if}
   </div>
  </div>
  <Login/>
  <Register/>
  

</div>