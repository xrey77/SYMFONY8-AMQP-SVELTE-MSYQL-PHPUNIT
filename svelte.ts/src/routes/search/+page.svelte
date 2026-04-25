<script lang="ts">
import Footer from '$lib/components/Footer.svelte';
  import type { FormEventHandler } from 'svelte/elements';
import axios from 'axios';

const api = axios.create({
    baseURL: "http://127.0.0.1:8000",
    headers: {'Accept': 'application/json',
            'Content-Type': 'application/json'}
})

let message: string = '';
let search: any = '';
let prods: any[] = [];

  function formatToDecimal(xval: any){
    const formatter = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
    return formatter.format(xval);
  }
    
  async function searchProducts(key: any) {
    message = "please wait...searching...";
    await api.get(`/api/productsearch/${key}`)
      .then((res: any) => {
          prods = res.data;
          window.setTimeout(() => {
            message = '';
          }, 1000);

      }, (error: any) => {
          if (error.response) {
            message = error.response.data.message;
          } else {
            message = error.message;
          }
          window.setTimeout(() => {
            message = '';
            prods = [];
          }, 1000);

      });    
  } 

  const submitSearchForm: FormEventHandler<HTMLFormElement> = (event) => {      
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const data = Object.fromEntries(formData.entries());  
    search = data.search;  
    searchProducts(search);
  }


</script>

<div class="container-fluid bg-dark x-height">
    <h3 class="text-white">Search Product</h3>
    
    <form onsubmit={submitSearchForm} class="row g-3" autocomplete="off">
        <div class="col-auto">
          <input type="text" class="form-control-sm" id="search" name="search" required placeholder="enter description key"/>
          <div class="text-left text-white mb-2">{message}</div>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary btn-sm mb-3">search</button>
        </div>

    </form>

    <div class="card-group mt-4 bg-dark card-group-center">

    {#each prods as product}
     <div class="col-md-4">
        <div class="card mt-4 mx-1">
          <img src={`http://127.0.0.1:8000/products/${product.productpicture}`} class="card-img-top" alt="..."/>
          <div class="card-body">
            <h5 class="card-title">Descriptions</h5>
            <p class="card-text">{product.descriptions}</p>
          </div>
          <div class="card-footer">
              <p class="card-text text-danger"><span class="text-dark">PRICE :</span>&nbsp;<strong>&#8369;{formatToDecimal(product.sellprice)}</strong></p>
          </div>  
        </div>
      </div>        
    {/each}

    </div>    

</div>    
{#if search !== ''}
<div class="bg text-white">
  <Footer/>
</div>
{/if}

<style lang="scss">
  .bg {    
    background-color: gray !important;
    background-size: cover !important;
  }
  .x-height {
    height: 200vh !important;
  }
</style>
