export const useUrl = (props, book_id = null) => {
  const endpoint = process.env.REACT_APP_API_URL;
  var url = null;

  switch (props) {
    case 'get_public_books':
      url = `${endpoint}/public/books`;
      break;

    case 'book_operation':
      url = `${endpoint}/books`;
      break;

    case 'book_detail_operation':
      url = `${endpoint}/books/${book_id}`;
      break;

    case 'comment_operation':
      url = `${endpoint}/books/${book_id}/comment`;
      break;

    case 'good_operation':
      url = `${endpoint}/comment/fluctuationLikes`;
      break;

    case 'user_operation':
      url = `${endpoint}/user`;
      break;

    case 'login':
      url = `${endpoint}/login`;
      break;

    case 'signup':
      url = `${endpoint}/signup`;
      break;

    case 'icon_upload':
      url = `${endpoint}/upload`;
      break;
  }

  return url;
};
