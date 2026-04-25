<script lang="ts">
import Footer from '$lib/components/Footer.svelte';
import axios from 'axios';
import { onMount } from 'svelte';

const api = axios.create({
    baseURL: "http://127.0.0.1:8000",
    headers: {'Accept': 'application/json',
            'Content-Type': 'application/json'}
})

let page: number = 1;
let totpage: number = 0;
let totrecs: number = 0;
let message: string = '';
let isfound: boolean = false;
let prods: any[] = [];

const fetchProducts = async (pg: any) => {
    message = "Please wait...";
    await api.get(`/api/productlist/${pg}`)
      .then((res: any) => {        
          prods = res.data.products;
          totrecs = res.data.totrecs;
          totpage = res.data.totpage;
          page = res.data.page;
          isfound = true;
      }, (error: any) => {
          if (error.response) {
            message = error.response.data.message;
          } else {
            message = error.message;
          }
          window.setTimeout(() => {
              message = '';
          }, 3000);              
      });
  }

  onMount(() => {
    fetchProducts(page);
  })


  const firstPage = (event: any) => {
    event.preventDefault();
    page = 1;
    fetchProducts(page);
    return;
  }

  const nextPage = (event: any) => {        
    event.preventDefault();
    if (page === totpage) {
        return;
    } 
    let pg = page + 1;
    page = pg;
    fetchProducts(page);
 }

 const prevPage = (event: any) => {
    event.preventDefault();
    let pg = page - 1;
    page = pg;
    fetchProducts(page);
    return;
}

const lastPage = (event: any) => {
    event.preventDefault();
    page = totpage;
    fetchProducts(page);
    return;
}

function formatToDecimal(xval: any) {
  const formatter = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  return formatter.format(xval);
}

</script>

<div class="container-fluid bg mb-1">
    <h3 class="text-white">Products Catalog</h3>

    {#if isfound === false}
      <div class="text-left text-danger">{message}</div>
    {/if}
    <div class="container-fluid">
      <div class="card-group mt-4 card-group-center">
      {#each prods as product}
          <div class="col-md-4">
            <div class="card mt-4 mx-1">
              <img src={`http://127.0.0.1:8000/products/${product.productpicture}`} class="card-img-top" alt="..."/>
              <div class="card-body">
                  <h5 class="card-title text-dark">Descriptions</h5>
                  <p class="card-text text-dark">{product.descriptions}</p>
              </div>
              <div class="card-footer">
                  <p class="card-text text-danger"><span class="text-dark">PRICE :</span>&nbsp;<strong>&#8369;{formatToDecimal(product.sellprice)}</strong></p>
              </div>  
            </div>
          </div>          
      {/each}
      </div>    
    </div>
     <nav aria-label="Page navigation example">
        <ul class="pagination mt-2">
          <li class="page-item"><button type="button" onclick={lastPage} class="page-link">Last</button></li>
          <li class="page-item"><button type="button" onclick={prevPage} class="page-link">Previous</button></li>
          <li id="next" class="page-item"><button type="button" onclick={nextPage} class="page-link">Next</button></li>
          <li class="page-item"><button type="button" onclick={firstPage} class="page-link">First</button></li>
          <li class="page-item page-link text-danger">Page&nbsp;{page} of&nbsp;{totpage}</li>
        </ul>
      </nav><br/>
</div>    
<div class="bg text-white">
    <Footer/>
</div>
 
<style lang="scss">
  .bg {    
    background-color: gray !important;
    background-size: cover !important;
  }
  .pagination {
    margin-left: 15px;
  }
</style>