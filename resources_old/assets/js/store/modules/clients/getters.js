export default {
  search: state => term => state.clients.filter(client =>
    Object.values(client).some(key =>
      String(key).toLowerCase().indexOf(term.toLowerCase()) !== -1
    )
  ),
};
