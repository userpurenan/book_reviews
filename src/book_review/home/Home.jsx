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
  const [cookies] = useCookies();
  const search = useLocation().search;
  const query = new URLSearchParams(search);
  const title_keyword = query.get('title_keyword');
  const get_public_books_url = useUrl('get_public_books'); //書籍取得APIのURL
  const get_books_url = useUrl('get_books'); //上戸同じ
  const auth = useSelector((state) => state.auth.isSignIn);
  const currentPage = useSelector((state) => state.pagenation.currentPage); //初期値は「０」
  const dispatch = useDispatch();

  const headers = {
    authorization: `Bearer ${cookies.token}`
  };

  //useEffect(「ここにasync入れたらダメ。」())
  useEffect(() => {
    const axiosData = async () => {
      var response;
      try {
        //ログインしていたら認証情報が必要なAPIから情報を取得する
        if (auth) {
          response = await axios.get(get_books_url, {
            headers,
            params: {
              title_keyword: title_keyword
            }
          });
        } else {
          response = await axios.get(get_public_books_url, {
            params: {
              title_keyword: title_keyword
            }
          });
        }

        setBooks(response.data);
      } catch (error) {
        alert(`書籍の取得に失敗しました${error}`);
      }
    };
    axiosData();
  }, []);

  const handlePagenation = async (offset, e) => {
    try {
      const response = await axios.get(get_public_books_url, {
        params: {
          offset: offset, // ここにクエリパラメータを指定する。
          title_keyword: title_keyword
        }
      });
      setBooks(response.data);
      e.target.id === 'next' ? dispatch(nextPagenation()) : dispatch(beforePagenation());
    } catch (error) {
      alert(`次のページの取得に失敗しまいしました${error}`);
    }
  };

  return (
    <div className="page">
      <Header />
      <h1>書籍レビュー一覧</h1>
      <div className="float_page">
        <form>
          <input
            className="search"
            type="text"
            name="title_keyword"
            defaultValue={title_keyword}
            placeholder="書籍のタイトルを入力"
          />
          <button type="submit" className="search_button">
            <IconContext.Provider value={{ size: '15px' }}>
              <AiOutlineSearch />
            </IconContext.Provider>
          </button>
        </form>
        <ul className="Book">
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
          onClick={(e) => {
            Pagenation((currentPage - 1) * 10, e);
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
      <input type="text" className="pagenation__currentPage" value={currentPage + 1} readOnly />
      {Books.length === 10 ? (
        <button
          id="next"
          onClick={(e) => {
            Pagenation((currentPage + 1) * 10, e);
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
