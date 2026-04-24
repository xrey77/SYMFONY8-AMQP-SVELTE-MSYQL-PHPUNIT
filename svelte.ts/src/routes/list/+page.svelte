<script lang="ts">
import Footer from "$lib/components/Footer.svelte";
import axios from 'axios'
 import { onMount } from 'svelte';

 function formatToDecimal(xval: any) {
    const formatter = new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2, // Ensures at least two decimal places
      maximumFractionDigits: 2, // Limits to two decimal places
    });
    return formatter.format(xval);
  }

 let page: number = 1;
 let totpage: number = 0;
 let message: string = '';
 let isfound: boolean = false;
 let prods: any[] = [];

 const api = axios.create({
    baseURL: "http://127.0.0.1:8000",
    headers: {'Accept': 'application/json',
            'Content-Type': 'application/json'}
})

const fetchProducts = (pg: any) => {
    isfound = false;
    message = "Please wait...";
    api.get(`/api/productlist/${pg}`)
      .then((res: any) => {        
          prods = res.data.products;
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
</script>

<div class="container">
    <h3 class="mt-3 mb-2">Product List</h3>
    {#if isfound === false}
      <div class="text-left text-danger">{message}</div>
    {/if}
    <table class="table table-striped mt-4">
    <thead class="table-primary">
        <tr>
        <th scope="col">#</th>
        <th scope="col">Descriptions</th>
        <th scope="col">Stocks</th>
        <th scope="col">Unit</th>
        <th scope="col">Price</th>
        </tr>
    </thead>
    <tbody>
        {#each prods as product}            
            <tr>
            <td>{product.id}</td>
            <td>{product.descriptions}</td>
            <td>{product.qty}</td>
            <td>{product.unit}</td>
            <td><strong class="text-danger">&#8369;</strong>{formatToDecimal(product.sellprice)}</td>
            </tr>          
        {/each}
    </tbody>
    </table>
    
     <nav aria-label="Page navigation example">
        <ul class="pagination">
          <li class="page-item"><button type="button" onclick={lastPage} class="page-link">Last</button></li>
          <li class="page-item"><button type="button" onclick={prevPage} class="page-link">Previous</button></li>
          <li id="next" class="page-item"><button type="button" onclick={nextPage} class="page-link">Next</button></li>
          <li class="page-item"><button type="button" onclick={firstPage} class="page-link">First</button></li>
          <li class="page-item page-link text-danger">Page&nbsp;{page} of&nbsp;{totpage}</li>
        </ul>
      </nav>
  </div>    
<div class="fixed-bottom mb-3">
    <Footer/>
</div>
