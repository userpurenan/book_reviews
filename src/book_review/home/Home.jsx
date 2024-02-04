import React, { useState, useEffect } from 'react';
import { useCookies } from 'react-cookie';
import { IconContext } from 'react-icons';
import { AiOutlineSearch } from 'react-icons/ai';
import { useDispatch, useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import { useLocation } from 'react-router-dom';
import axios from 'axios';
import PropTypes from 'prop-types';
import { beforePagenation, nextPagenation } from '../../pagenationSlice';
import { useUrl } from '../../useUrl';
import { Header } from '../header/Header';
import './Home.scss';

export const Home = () => {
  const [Books, setBooks] = useState([]);
  const [errorMessage, setErrorMessage] = useState();
  const [cookies] = useCookies();
  const search = useLocation().search;
  const query = new URLSearchParams(search);
  const titleKeyword = query.get('title_keyword');
  const getBooksUrl = useUrl('bookOperation'); //カスタムフック。このコンポーネントで使うapiのurlが返る
  const currentPage = useSelector((state) => state.pagenation.currentPage); //初期値は「０」
  const dispatch = useDispatch();

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  //useEffect(「ここにasync入れたらダメ。」())
  useEffect(() => {
    axios
      .get(getBooksUrl, {
        headers,
        params: {
          title_keyword: titleKeyword
        }
      })
      .then((response) => {
        setBooks(response.data);
      })
      .catch((error) => {
        setErrorMessage(`書籍の取得に失敗しました${error}`);
      });
  }, []);

  const handlePagenation = (offset, event) => {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });

    axios
      .get(getBooksUrl, {
        params: {
          offset: offset, // ここにクエリパラメータを指定する。
          title_keyword: titleKeyword
        }
      })
      .then((response) => {
        setBooks(response.data);
        event.target.id === 'next' ? dispatch(nextPagenation()) : dispatch(beforePagenation());
      })
      .catch((error) => {
        setErrorMessage(`次のページの取得に失敗しました${error}`);
      });
  };

  return (
    <div className="page">
      <Header />
      <h1 className="book_home_h1">書籍レビュー一覧</h1>
      <p className="error-message">{errorMessage}</p>
      <div className="extend_float_page">
        <form className="search">
          <input
            className="search_input"
            type="text"
            name="title_keyword"
            defaultValue={titleKeyword}
            placeholder="書籍のタイトルを入力"
          />
          <button type="submit" className="search_button">
            <IconContext.Provider value={{ size: '15px' }}>
              <AiOutlineSearch />
            </IconContext.Provider>
          </button>
        </form>
        <ul>
          {Books.map((BookList, key) => (
            <li key={key} className="Book__list" value={BookList.id}>
              <Link to={`/detail/${BookList.id}`} className="Book__list--link">
                {BookList.title}
              </Link>
            </li>
          ))}
        </ul>
        <Pagination currentPage={currentPage} Pagenation={handlePagenation} Books={Books} />
      </div>
    </div>
  );
};

const Pagination = ({ currentPage, Pagenation, Books }) => {
  return (
    <div className="pagenation">
      {currentPage !== 0 ? (
        <button
          id="before"
          onClick={(event) => {
            Pagenation(currentPage - 10, event);
          }}
          className="pagenation__button"
        >
          前のページへ
        </button>
      ) : (
        <button className="pagenation__button" disabled>
          前のページへ
        </button>
      )}

      {/* 以下のvalueの計算式について補足。1を足さないと、currentpageが０の時に表示される現在のページが０になってしまい、数字が一つずれてしまう。 */}
      <input
        type="text"
        className="pagenation__currentPage"
        value={currentPage / 10 + 1}
        readOnly
      />
      {Books.length === 10 ? (
        <button
          id="next"
          onClick={(event) => {
            Pagenation(currentPage + 10, event);
          }}
          className="pagenation__button"
        >
          次のページへ
        </button>
      ) : (
        <button className="pagenation__button" disabled>
          次のページへ
        </button>
      )}
    </div>
  );
};

Pagination.propTypes = {
  currentPage: PropTypes.number,
  Pagenation: PropTypes.func.isRequired,
  Books: PropTypes.array
};

export default Home;
