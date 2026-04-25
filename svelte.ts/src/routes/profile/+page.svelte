<script lang="ts">
  import type { FormEventHandler } from 'svelte/elements';
  import axios from 'axios';
  import { onMount } from 'svelte';
  import jQuery from 'jquery';

  let userid = $state('');
  let firstname = $state('');
  let lastname = $state('');
  let email = $state('');
  let mobile = $state('');
  let message = $state('');
  let showSave = $state(false);
  let userpicture = $state('');
  let chgPwd = $state(false);
  let chkMfa = $state(false);
  let qrcodeurl = $state('');
  let token = $state('');
  let selectedFile: File | null = null;
  let newpassword = $state('');
  let confnewpassword = $state('');
  let cbpwd: any = null;
  let cbmfa: any = null;

 const api = axios.create({
   baseURL: "http://127.0.0.1:8000",
   headers: {'Accept': 'application/json',
             'Content-Type': 'application/json'}
 });

 const mapi = axios.create({
   baseURL: "http://127.0.0.1:8000",
 });

interface User {
    id: string,
    firstname: string,
    lastname: string,
    email: string,
    mobile: string,
    userpicture: string,
    qrcodeurl: string,
}

interface Userdata {
  statuscode: string,
  message: string,
  user: User
}

 const getUserinfo = (usrid: any, tken: any) => {
   jQuery('#chkPwd').prop('checked', false);
   jQuery('#chkMfa').prop('checked', false);
   chgPwd = false;
   chkMfa = false;
   message = "Please wait.....";
   api.get<Userdata>(`/api/getuserid/${usrid}`, { headers: {
       Authorization: `Bearer ${tken}`
   }} )
       .then((res: any) => {
            message = res.data.message;
            firstname = res.data.user.firstname;
            lastname = res.data.user.lastname;
            email = res.data.user.email;
            mobile = res.data.user.mobile; 
            userpicture = `http://127.0.0.1:8000/users/${res.data.user.userpic}`;
            if (res.data.user.qrcodeurl === null) {
              qrcodeurl = `http://127.0.0.1:8000/images/qrcode.png`;
            } else {
              qrcodeurl = res.data.user.qrcodeurl;
            }
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
              }, 3000);             
       });    
 }

  onMount(() => {
      const uid = sessionStorage.getItem('USERID');
      if (uid !== null) {
        userid = uid;
      } else {
        userid = '';
      }

      const tken = sessionStorage.getItem('TOKEN');
      if (tken !== null) {
          token = tken;
      }  else {
          token = '';
      }    
      getUserinfo(userid, token);
  });

 const submitProfile: FormEventHandler<HTMLFormElement> = (event) => {      
   event.preventDefault();
   const formData = new FormData(event.currentTarget);
   const data = Object.fromEntries(formData.entries());    


   const jdata =JSON.stringify({ lastname: data.lastname, 
       firstname: data.firstname, mobile: data.mobile });

   api.patch(`/api/updateprofile/${userid}`, jdata, { headers: {
   Authorization: `Bearer ${token}`
   }})
   .then((res: any) => {
           message = res.data.message;
           window.setTimeout(() => {
              message = '';
            }, 2000);
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
 };

 function changePicture(event: any) {
   const input = event.target as HTMLInputElement;
   if (input.files && input.files[0]) {
     selectedFile = input.files[0];
    const pix = URL.createObjectURL(selectedFile);
      jQuery('#userpic').attr('src', pix);
   }

   if (selectedFile) {
       let formdata = new FormData();
       formdata.append('id', userid);
       formdata.append('userpic', selectedFile);
       mapi.post(`/api/uploadpicture`, formdata, { 
          headers: {
            Authorization: `Bearer ${token}`
          }
        })
       .then((res: any) => {
           message = res.data.message;
           let userpic: string = `http://127.0.0.1:8000/users/${res.data.userpicture}`;
           userpicture = userpic;
           sessionStorage.setItem('USERPIC', userpic);
           window.setTimeout(() => {
             message = '';
             window.location.reload();
           }, 3000);    
       }, (error) => {
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
 }

 function checkboxPassword(e: any) {
   if (e.target.checked) {
       chgPwd = true
       cbpwd = 'checked';
       cbmfa = null;
       chkMfa = false;
       showSave = false;
       jQuery('#chkMfa').prop('checked', false);
   } else {
       chgPwd = false;
       cbpwd = null;
       cbmfa = null;
       chkMfa = false;
       jQuery("#newpassword").val('');
       jQuery("#confnewpassword").val('');
   }
 }

 function checkboxMFA(event: any): void {
   if (event.target.checked) {
       jQuery('#chkPwd').prop('checked', false);
       chkMfa = true;
       cbmfa = 'checked';
       chgPwd = false;
       cbpwd = '';
       showSave = true;        
   } else {
       chkMfa = false;
       cbmfa = ''
       cbpwd = '';
       chgPwd =false;
       showSave = false;        
   }
 }

const changePassword: FormEventHandler<HTMLFormElement> = (event) => {      
   event.preventDefault();
   const formData = new FormData(event.currentTarget);
   const jdata = Object.fromEntries(formData.entries());    

   event.preventDefault();
   if (jdata.newpassword === '') {
       message = "Please enter New Password.";
       setTimeout(() => {
         message = '';
       }, 3000);
       return;
   }
   if (jdata.confnewpassword === '') {
       message = "Please confirm New Password.";
       setTimeout(() => {
         message = '';
       }, 3000);
       return;
   }
   if (jdata.newpassword != jdata.confnewpassword) {
       message = "New Password does not matched.";
       setTimeout(() => {
         message = '';
         jQuery("#newpassword").val('');
         jQuery("#confnewpassword").val('');
       }, 3000);
       return;
   }
   message = 'Please wait...';
   const data =JSON.stringify({ password: jdata.newpassword });
   api.patch(`/api/changeuserpassword/${userid}`, data, { 
    headers: {
      Authorization: `Bearer ${token}`
    }
  })
   .then((res: any) => {
         message = res.data.message;
         window.setTimeout(() => {
          message = '';
        }, 2000);

     }, (error: any) => {
        if (error.response) {
          message = error.response.data.message;
        } else {
          message = error.message;
        }
        window.setTimeout(() => {
          message = '';
          newpassword = '';
          confnewpassword = '';
        }, 3000);          
           
   });
 }

  async function enableMFA(e: any) {
   e.preventDefault();
   const data =JSON.stringify({ Twofactorenabled: true });
   await api.patch(`/api/activationmfa/${userid}`, data, { headers: {
   'Content-Type': 'application/json',
   Authorization: `Bearer ${token}`
   }} )
   .then((res: any) => {
       message = res.data.message;
       window.setTimeout(() => {
           message = '';
           qrcodeurl = res.data.qrcodeurl;
       }, 3000);

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

 async function disableMFA(e: Event) {
   e.preventDefault();
   const jdata =JSON.stringify({ Twofactorenabled: false });
   await api.patch(`/api/activationmfa/${userid}`, jdata, { headers: {
       Authorization: `Bearer ${token}`
   }} )
   .then((res: any) => {
     message = res.data.message;
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
         qrcodeurl = 'http://127.0.0.1:8000/images/qrcode.png';
       }, 3000);
   });
 }
</script>

<div class="container mt-2 mb-5">
 <div class="card card-width bs-info-border-subtle mb-4">
     <div class="card-header bg-success text-white">
       <strong>USER'S PROFILE NO.&nbsp; {userid}</strong>
     </div>
     <div class="card-body bg-warning">
 
       <form onsubmit={submitProfile} enctype='multipart/form-data' autocomplete='off'>
           <div class="row">
               <div class="col">
                   <div class="mb-3">
                       <input type="text" id="firstname" name="firstname" required value={firstname} class="form-control"  autocomplete="off"/>
                   </div>
                   <div class="mb-3">
                       <input type="text" required id="lastname" name="lastname" value={lastname} class="form-control" autocomplete="off"/>
                   </div>
                   <div class="mb-3">
                       <input type="email" id="emal" name="email" value={email} class="form-control" readonly/>
                   </div>
                   <div class="mb-3">
                       <input type="text" required id="mobile" name="mobile" value={mobile} class="form-control" autocomplete="off"/>
                   </div>
                   <div class="mb-3">
                     {#if showSave == false}                    
                       <button type="submit" class="btn btn-success text-white">save</button>
                     {/if}
                   </div>
 
               </div>
               <div class="col">
                   <img id="userpic" class="usr" src={userpicture as string} alt=""/>
                   <div class="mb-3">
                       <input type="file" multiple accept="image/*" onchange={changePicture} class="form-control form-control-sm mt-3"/>
                   </div>
 
               </div>
               <div class="mb-3">
             </div>
 
           </div>
         </form>
 
           <div class="row">
               <div class="col">
                   <div class="form-check">
                       <input id="chkPwd" onchange={checkboxPassword} type="checkbox" class="form-check-input bcolor" />
                       <label class="form-check-label" for="chgPwd">
                           Change Password
                       </label>
                   </div>
               </div>
               <div class="col">
                   <div class="form-check">
                       <input class="form-check-input bcolor" type="checkbox" id="chkMfa"  onchange={checkboxMFA} />
                       <label class="form-check-label" for="chkMfa">
                           Multi-Factor Authenticator
                       </label>
                   </div>
               </div>
           </div>
 
           <div class="row">
 
               <div class="col">
 
                 {#if chgPwd}
                   <form onsubmit={changePassword} autocomplete="off">
                   <div class="mb-3">
                       <input type="password" id="newpassword" name="newpassword" class="form-control pwd mt-2" placeholder="enter new Password" autocomplete="off"/>
                   </div>
                   <div class="mb-3">
                       <input type="password" id="confnewpassword" name="confnewpassword" class="form-control pwd" placeholder="confirm new Password" autocomplete="off"/>
                   </div>
                   <button type="submit" class="btn btn-primary">change password</button>
                 </form>
                 {/if}
 
                 {#if chkMfa}
                     <img class="qrcode1" src={qrcodeurl} alt=""/>
                 {/if}
               </div>
 
               <div class="col">
                   {#if chkMfa == true}
                           <p id="qrcode-cap1" class='text-danger'><strong>Requirements</strong></p>
                           <p id="qrcode-cap2">You need to install <strong>Google or Microsoft Authenticator</strong> in your Mobile Phone, once installed, click Enable Button below, and <strong>SCAN QR CODE</strong>, next time you login, another dialog window will appear, then enter the <strong>OTP CODE</strong> from your Mobile Phone in order for you to login.</p>
                           <div class="row">
                               <div class="col">
                                   <button onclick={enableMFA} type="button" class="btn btn-primary qrcode-cap3 mx-2">Enable</button>
                                   <button onclick={disableMFA} type="button" class="btn btn-secondary qrcode-cap3">Disable</button>
                                 </div>
                           </div>
                   {/if}
               </div>
           </div>

     </div>
     {#if message != ''}        
      <div class="card-footer">
        <div class="w-100 text-left text-danger">{message}</div>
      </div>
     {/if}
 
   </div>    
 </div>
