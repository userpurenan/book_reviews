import React, { useState } from 'react';
import { useCookies } from 'react-cookie';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';
import { useUrl } from '../../useUrl';
import { BookReviewInput } from '../book_review_input/BookReviewInput';
import { Header } from '../header/Header';
import './CreateBookReview.scss';

export const CreateBookReview = () => {
  const navigate = useNavigate();
  const [cookies] = useCookies();
  const [bookTitle, setBookTitle] = useState('');
  const [bookDetail, setBookDetail] = useState('');
  const [bookReview, setBookReview] = useState('');
  const [isSpoiler, setIsSpoiler] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const createBookUrl = useUrl('bookOperation'); //カスタムフック。このコンポーネントで使うapiのurlが返る

  const createBook = async () => {
    const rakutenBookSearchAPI = useUrl('bookSerchAPI', null, bookTitle);
    const response = await axios.get(rakutenBookSearchAPI);

    if(Object.keys(response.data.Items).length === 0) {
      return setErrorMessage(`「${bookTitle}」というタイトルの書籍は見つかりませんでした。`);
    }

    const data = {
      title: bookTitle,
      url: response.data.Items[0].Item.itemUrl,
      detail: bookDetail,
      review: bookReview,
      isSpoiler: isSpoiler
    };

    axios
      .post(createBookUrl, data, {
        headers: {
          authorization: `Bearer ${cookies.token}`
        }
      })
      .then(() => {
        navigate('/');
      })
      .catch((error) => {
        setErrorMessage(`エラー発生 ${error}`);
      });
  };

  return (
    <div className="page">
      <Header />
      <h1 className="create_book_h1">書籍レビュー新規投稿</h1>
      <h2 className="error-massage">{errorMessage}</h2>
      <BookReviewInput
        isSpoiler={isSpoiler}
        setBookTitle={setBookTitle}
        setBookDetail={setBookDetail}
        setBookReview={setBookReview}
        setIsSpoiler={setIsSpoiler}
        BookOperations={createBook}
        isCreateBook={true}
      />
    </div>
  );
};
