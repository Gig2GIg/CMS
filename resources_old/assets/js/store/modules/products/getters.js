export default {
  search: state => term => state.products.filter(product =>
    Object.values(product).some(key =>
      String(key).toLowerCase().indexOf(term.toLowerCase()) !== -1
    )
  ),
};
