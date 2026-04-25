<script lang="ts">
  import 'bootstrap/dist/css/bootstrap.min.css';
  import Header from '$lib/components/Header.svelte';

  // for spa purpose
  export const ssr = true;
  export const csr = true;
  export const prerender = false;

  let { children } = $props();
  let modalElement = $state();

  $effect(() => {
      import('bootstrap').then((bootstrap) => {
        const myModal = new bootstrap.Modal(modalElement);
      });
    });
</script>

<Header />

<main>
  {@render children()}
</main>


