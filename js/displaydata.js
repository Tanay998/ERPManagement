
  function showt(){
    if(document.getElementById('entry').value=="Jeep Entry")
    {
        document.getElementById('entry1').value=document.getElementById('entry').value;
      document.getElementById('tfw1').value = document.getElementById('tfw').value;
      //document.getElementById('para1').innerHTML = "Enter Your Highschool Details";
      document.getElementById('jeep1').style.display="block";
      document.getElementById('RollNo').style.display="block";
      document.getElementById('tfw1').style.display="block";
    }
    else if(document.getElementById('entry').value=="Lateral Entry")
    {
      document.getElementById('entry1').value=document.getElementById('entry').value;
      document.getElementById('tfw1').value = document.getElementById('tfw').value;
      //document.getElementById('para1').innerHTML = "Enter Your Intermediate Details";
      document.getElementById('jeep1').style.display="block";
      document.getElementById('RollNo').style.display="block";
      document.getElementById('tfw1').style.display="none";
    }
    else if(document.getElementById('entry').value=="Direct Entry"){
      document.getElementById('entry1').value=document.getElementById('entry').value;
      document.getElementById('tfw1').value = document.getElementById('tfw').value;
      document.getElementById('jeep1').style.display="none";
      document.getElementById('RollNo').style.display="none";
      document.getElementById('tfw1').style.display="none";
    }

  }
  function showing()
      {
        if(document.getElementById("diva").value=="NO")
        {
          document.getElementById("div2").style.display="none";
        }
        else if(document.getElementById("diva").value=="YES")
        {
          document.getElementById("div2").style.display="block";
        }
        document.getElementById("diva11").value = document.getElementById("diva").value;
      }
  

  function calculate() {
    if(document.getElementById('typeofmarks').value=="percent")
    {
      document.getElementById('typeofmarks11').value = document.getElementById('typeofmarks').value;
      document.getElementById('tbt').style.display="block";
      document.getElementById('percentage').value=parseInt((document.getElementById('obtainmarks').value*100)/document.getElementById('totalmarks').value);
    }
    if(document.getElementById('typeofmarks').value=="cgpa")
    {
      document.getElementById('typeofmarks11').value = document.getElementById('typeofmarks').value;
      document.getElementById('tbt').style.display="none";
      document.getElementById('percentage').value=parseInt((document.getElementById('obtainmarks').value*9.5));
    }
  }  


