<script lang="ts">

  import 'bootstrap/dist/css/bootstrap.min.css';
  import scriptSrc from 'bootstrap/dist/js/bootstrap.bundle.min.js?url';  
  import Header from '$lib/components/Header.svelte';
</script>

<svelte:head>
  <!-- Load the script directly in the browser's head -->
  <script src={scriptSrc} defer></script>
</svelte:head>

<Header />

<main>
  <slot />
</main>


