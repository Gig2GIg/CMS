export default {
  search: state => term => state.rentals.filter(client =>
    Object.values(client).some(key =>
      String(key).toLowerCase().indexOf(term.toLowerCase()) !== -1
    )
  ),
};
