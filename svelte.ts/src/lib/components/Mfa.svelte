<div class="modal fade" id="staticMfa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticMfaLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title fs-5" id="staticMfaLabel">Multi-Factor Authenticator</h5>
        <button onclick={closeMFA} type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form onsubmit={SubmitMfa} autocomplete="off">
            <div class="mb-3">
                <input type="text" required class="form-control border-warning" placeholder="enter 6-digit OTP">
            </div>                      
            <button type="submit" class="btn btn-warning">submit</button>
            <button type="reset" class="btn btn-warning">reset</button>
        </form>
      </div>
      <div class="modal-footer">
        <div class="w-100 text-center text-danger">{message as string}</div>
      </div>
    </div>
  </div>
</div>

<script lang="ts">
  import type { FormEventHandler } from 'svelte/elements';
  import axios from 'axios'

  // import { goto } from '$app/navigation';

  const api = axios.create({
    baseURL: "http://127.0.0.1:8000",
    headers: {'Accept': 'application/json',
        'Content-Type': 'application/json'}
  });

  let { 
    message = $bindable(''), 
    otp = $bindable(''), 
    userid = '', 
    token = '' 
  } = $props();

  const closeMFA = () => {
      sessionStorage.removeItem('USERID');
      sessionStorage.removeItem('USERNAME');
      sessionStorage.removeItem('TOKEN');
      sessionStorage.removeItem('USERPIC');
      otp = '';
      message = '';
  }

  const SubmitMfa: FormEventHandler<HTMLFormElement> = (event) => {      
    event.preventDefault();
    const uid = sessionStorage.getItem('USERID');
    userid = uid ?? '';
    
    const tken = sessionStorage.getItem('TOKEN');
    token = tken ?? '';
    
    const formData = new FormData(event.currentTarget);
    const jdata = Object.fromEntries(formData.entries());    
    const data =JSON.stringify({ otp: jdata.otp });
        api.patch(`/api/otpvalidation/${userid}`, data, { headers: {
        Authorization: `Bearer ${token}`
       }})
        .then((res: any) => {
            message = res.data.message;
            sessionStorage.setItem("USERNAME", res.data.username);
            window.setTimeout(() => {
              otp = '';
              message = '';
              // goto('/'); 
            },3000);
            return;
          }, (error: any) => {
               if (error.response) {
                message = error.response.data.message;
               } else {
                message = error.message;
               }
               window.setTimeout(() => {
                  message = '';
                  otp = '';
                }, 3000);    
                return;
        });            
  };
</script>