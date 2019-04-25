export default {
  search: state => term => state.categories.filter(category =>
    Object.values(category).some(key =>
      String(key).toLowerCase().indexOf(term.toLowerCase()) !== -1
    )
  ),
};
