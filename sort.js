Sorting a table - doesn't work with numbers, all sorted as text
<p><button id="but1" onclick="sortTable(1,'d')">Sort</button></p>

<table id="myTable">

<th id="col1" onclick="sortTable(1,'d')">Country</th>

<script>
function sortTable(col,updn) {
  var table, rows, switching, i, x, y, shouldSwitch;
  table = document.getElementById("myTable");
  switching = true;
  /*Make a loop that will continue until
  no switching has been done:*/
  while (switching) {
    //start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /*Loop through all table rows (except the
    first (0), which contains table headers):*/
    for (i = 1; i < (rows.length - 1); i++) {
      //start by saying there should be no switching:
      shouldSwitch = false;
      /*Get the two elements you want to compare,
      one from current row and one from the next:*/
      x = rows[i].getElementsByTagName("TD")[col];
      y = rows[i + 1].getElementsByTagName("TD")[col];
      //check if the two rows should switch place:
      if(updn == "u") {
      if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
        //if so, mark as a switch and break the loop:
        shouldSwitch = true;
        break;
      }
      }
      else {
      if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
        //if so, mark as a switch and break the loop:
        shouldSwitch = true;
        break;
      }
      }
    }
    if (shouldSwitch) {
      /*If a switch has been marked, make the switch
      and mark that a switch has been done:*/
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
  if(updn == "d") {
  document.getElementById( "but1" ).setAttribute( "onClick", "sortTable(1,'u');" );
  }
  else {
  document.getElementById( "but1" ).setAttribute( "onClick", "sortTable(1,'d');" );
}
}
</script>


Try up and down, text and numbers
<script>
function sortTable(col,updn,num) {
  var table, rows, switching, i, x, y, shouldSwitch;
  table = document.getElementById("myTable");
  switching = true;
  /*Make a loop that will continue until
  no switching has been done:*/
  while (switching) {
    //start by saying: no switching is done:
    switching = false;
    rows = table.rows;
    /*Loop through all table rows (except the
    first, which contains table headers):*/
    for (i = 1; i < (rows.length - 1); i++) {
      //start by saying there should be no switching:
      shouldSwitch = false;
      /*Get the two elements you want to compare,
      one from current row and one from the next:*/
      if(num == "n") {
		x = parseInt(rows[i].getElementsByTagName("TD")[col]); //these 2 lines don't work, try adding .textContent
		y = parseInt(rows[i + 1].getElementsByTagName("TD")[col]);
      }
      else {
		x = rows[i].getElementsByTagName("TD")[col]; //maybe put these before if then else is not required
		y = rows[i + 1].getElementsByTagName("TD")[col];
      }
      //check if the two rows should switch place:
      if(updn == "u") {
		if (x.innerHTML > y.innerHTML) {
			//if so, mark as a switch and break the loop:
			shouldSwitch = true;
			break;
		}
      }
      else {
		if (x.innerHTML < y.innerHTML) {
			//if so, mark as a switch and break the loop:
			shouldSwitch = true;
			break;
		}
      }
    }
    if (shouldSwitch) {
      /*If a switch has been marked, make the switch
      and mark that a switch has been done:*/
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
    }
  }
  if(updn == "d") {
  document.getElementById( "col"+col ).setAttribute( "onClick", "sortTable("+col+",'u', 'n');" );
  }
  else {
  document.getElementById( "col"+col ).setAttribute( "onClick", "sortTable("+col+",'d', 'n');" );
}
}
</script>