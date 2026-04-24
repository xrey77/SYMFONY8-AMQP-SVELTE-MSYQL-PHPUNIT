<script lang="ts">
  import Mfa from "./Mfa.svelte";
  import type { FormEventHandler } from 'svelte/elements';
  import axios from 'axios'

  import jQuery from 'jquery';
  // import { goto } from '$app/navigation';

  
  export let message: string = "";
  export let isdisable: boolean = false;

  const api = axios.create({
    baseURL: "http://127.0.0.1:8000",
    headers: {'Accept': 'application/json',
              'Content-Type': 'application/json'}
  });

interface User {
  statuscode: string,
  message: string,
  id: string,
  lastname: string,
  firstname: string,
  username: string,
  roles: string,
  isactivated: number,
  isblocked: number,
  profilepic: string,
  qrcodeurl: string,
  token: string
}

  function CloseLogin(event: any) {
    event.preventDefault();
    message = "";
    jQuery("#loginReset").trigger("click");
    window.location.reload();
  }

  const SubmitLogin: FormEventHandler<HTMLFormElement> = (event) => {      
    event.preventDefault();
    isdisable = true;
    message = "please wait...";
    const formData = new FormData(event.currentTarget);
    const data = Object.fromEntries(formData.entries());    
    const jsondata = JSON.stringify({ username: data.username, password: data.password });
         api.post<User>("api/login", jsondata)
        .then((res: any) => {
                message = res.data.message;
                let userpic: string = `http://127.0.0.1:8000/users/${res.data.userpicture}`;
                if (res.data.qrcodeurl !== null) {
                    sessionStorage.setItem('USERID',res.data.id);
                    sessionStorage.setItem('TOKEN',res.data.token);
                    sessionStorage.setItem('ROLE',res.data.roles);
                    sessionStorage.setItem('USERPIC',userpic);
                    message = '';                    
                    jQuery("#loginReset").trigger("click");
                    jQuery("#mfaModal").trigger("click");
                    window.setTimeout(() => {
                      window.location.replace('/'); 
                      window.location.reload();
                    }, 3000);

                } else {
                    sessionStorage.setItem('USERID',res.data.id);
                    sessionStorage.setItem('USERNAME',res.data.username);
                    sessionStorage.setItem('TOKEN',res.data.token);                        
                    sessionStorage.setItem('ROLE',res.data.roles);
                    sessionStorage.setItem('USERPIC',userpic);                    
                    CloseLogin;
                    message = '';                    
                    isdisable = false;
                    window.location.replace('/'); 
                    window.setTimeout(() => {
                      window.location.reload();
                    }, 3000);

                }
          }, (error: any) => {
              console.log(error);
                if (error.response) {
                  message = error.response.data.message;
                } else {
                  message = error.message;
                }               
                window.setTimeout(() => {
                    message = '';
                    isdisable = false;
                }, 3000);
                return;
        });   

  };

</script>
<div class="modal fade" id="staticLogin" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticLoginLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger">
        <h1 class="modal-title fs-5 text-white" id="staticLoginLabel">User Login</h1>
        <button on:click={CloseLogin} id="closeLogin" type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form on:submit={SubmitLogin} autocomplete="off">
            <div class="mb-3">
              <input type="text" required class="form-control border-danger" id="username" name="username" placeholder="enter Username" disabled={isdisable}>
            </div>            
            <div class="mb-3">
                <input type="password" required class="form-control border-danger" id="password" name="password" placeholder="enter Password" disabled={isdisable}>
            </div>              
            <button type="submit" class="btn btn-danger" disabled={isdisable}>login</button>
            <button id="loginReset" type="reset" class="btn btn-danger">reset</button>
            <button id="mfaModal" type="reset" class="btn d-none" data-bs-toggle="modal" data-bs-target="#staticMfa">mfa</button>
        </form>
      </div>
      <div class="modal-footer">

        <div class="w-100 text-center text-danger">{message as string}</div> 
      </div>
    </div>
  </div>
</div>
<Mfa/>

