<script lang="ts">
import Footer from '$lib/components/Footer.svelte';
  import type { FormEventHandler } from 'svelte/elements';
import axios from 'axios';

const api = axios.create({
    baseURL: "http://127.0.0.1:8000",
    headers: {'Accept': 'application/json',
            'Content-Type': 'application/json'}
})

let page: number = 1;
let totpage: number = 0;
let totalrecords: number = 0;
let message: string = '';
let search: any = '';
let isfound: boolean = false;
let prods: any[] = [];

  function formatToDecimal(xval: any){
    const formatter = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });
    return formatter.format(xval);
  }
    
  async function searchProducts(page: any, key: any) {
    message = "please wait...searching...";
    await api.get(`/api/productsearch/${page}/${key}`)
      .then((res: any) => {
          prods = res.data.products;
          totpage = res.data.totpage;
          totalrecords = res.data.totalrecords;
          page = res.data.page;
          isfound = true;
          if (totpage === 0) {
            message = "keyword not found....";
          }
          window.setTimeout(() => {
            message = '';
          }, 3000);

      }, (error: any) => {
          if (error.response) {
            message = error.response.data.message;
          } else {
            message = error.message;
          }
          window.setTimeout(() => {
            message = '';
            isfound = false;
            prods = [];
          }, 3000);

      });    
  } 

  const submitSearchForm: FormEventHandler<HTMLFormElement> = (event) => {      
    event.preventDefault();
    const formData = new FormData(event.currentTarget);
    const data = Object.fromEntries(formData.entries());  
    search = data.search;  
    searchProducts( page, search);
  }

  const firstPage = (event: any) => {
    event.preventDefault();
    page = 1;
    searchProducts(page, search);
    return;
  }

  const nextPage = async (event: any) => {        
    event.preventDefault();
    if (page === totpage) {
        return;
    } 
    let pg = page + 1;
    page = pg;
    await searchProducts(page, search);
  }

  const prevPage = (event: any) => {
    event.preventDefault();
    let pg = page - 1;
    page = pg;
    searchProducts(page, search);
    return;
  }

  const lastPage = (event: any) => {
    event.preventDefault();
    page = totpage;
    searchProducts(page, search);
    return;
  }

</script>

<div class="container-fluid bg">
    <h3 class="text-white">Search Product</h3>
    <!-- {#if isfound === false}  -->
      <div class="text-left text-danger mb-2">{message}</div>
    <!-- {/if} -->
    
    <form onsubmit={submitSearchForm} class="row g-3" autocomplete="off">
        <div class="col-auto">
          <input type="text" class="form-control-sm" id="search" name="search" required placeholder="enter description key"/>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary btn-sm mb-3">search</button>
        </div>

    </form>

    <div class="card-group bg">

    {#each prods as product}
     <div class="col-md-4">
        <div class="card card-height mb-2 mx-1">
          <img src={`http://localhost:8080/products/${product.productpicture}`} class="card-img-top" alt="..."/>
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

    {#if totpage}
     <nav aria-label="Page navigation example">
      <ul class="pagination">
        <li class="page-item"><button type="button" onclick={lastPage} class="page-link">Last</button></li>
        <li class="page-item"><button type="button" onclick={prevPage} class="page-link">Previous</button></li>
        <li id="next" class="page-item"><button type="button" onclick={nextPage} class="page-link">Next</button></li>
        <li class="page-item"><button type="button" onclick={firstPage} class="page-link">First</button></li>
        <li class="page-item page-link text-danger">Page&nbsp;{page} of&nbsp;{totpage}</li>
     </ul>
    </nav> 
    <br/>
  {/if}
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
</style>
