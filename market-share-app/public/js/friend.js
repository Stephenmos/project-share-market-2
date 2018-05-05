  
  //ajax to retrieve insertFriend() from FriendController
  function addAjax(fid){
    $.ajax({
      url: "/community/add/" + fid,
      type: 'get',
      success: function(data) {
        if (data) {
          console.log("Value Added");
          window.location.reload();
        }
        else {
          alert("Friend already exists");
        }
      }
    });
  }

  //ajax to retrieve deleteFriend() from FriendController
  function deleteAjax(fid) {
    $.ajax({
      url: "/community/delete/" + fid,
      type: 'get',
      success: function () {
        console.log("Value Removed");
        window.location.reload();
      }
    });
  }