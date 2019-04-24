export default {
  search: state => term => state.renters.filter(renter =>
    Object.values(renter).some(key =>
      String(key).toLowerCase().indexOf(term.toLowerCase()) !== -1
    )
  ),
};
