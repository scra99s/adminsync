document.addEventListener('DOMContentLoaded', () => {

  fetch(OC.generateUrl('/apps/adminsync/settings'))
    .then(res => res.json())
    .then(data => {
      document.getElementById('admin_groups').value = JSON.parse(data.admin_groups).join(',');
      document.getElementById('protected_admins').value = JSON.parse(data.protected_admins).join(',');
    });

  document.getElementById('save').addEventListener('click', () => {

    const adminGroups = document.getElementById('admin_groups').value;
    const protectedAdmins = document.getElementById('protected_admins').value;

    fetch(OC.generateUrl('/apps/adminsync/settings'), {
      method: 'POST',
      headers: {
        'requesttoken': OC.requestToken,
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      body: `admin_groups=${encodeURIComponent(adminGroups)}&protected_admins=${encodeURIComponent(protectedAdmins)}`
    })
      .then(() => alert('Saved'));
  });
});
