export const useUrl = (props, book_id = null) => {
  const endpoint = process.env.REACT_APP_API_URL;
  var url = null;

  switch (props) {
    case 'getPublicBooks':
      url = `${endpoint}/public/books`;
      break;

    case 'bookOperation':
      url = `${endpoint}/books`;
      break;

    case 'bookDetailOperation':
      url = `${endpoint}/books/${book_id}`;
      break;

    case 'commentOperation':
      url = `${endpoint}/books/${book_id}/comment`;
      break;

    case 'updateLikes':
      url = `${endpoint}/comment/updateLikes`;
      break;

    case 'userOperation':
      url = `${endpoint}/user`;
      break;

    case 'login':
      url = `${endpoint}/login`;
      break;

    case 'signUp':
      url = `${endpoint}/signup`;
      break;

    case 'iconUpload':
      url = `${endpoint}/upload`;
      break;
  }

  return url;
};
